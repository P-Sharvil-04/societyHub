<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class document_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('document_model');
		$this->load->library(['upload', 'session']);
		$this->load->helper(['url', 'form']);
	}

	public function index()
	{
		$data['documents'] = $this->document_model->get_all();

		$id = $this->input->get('id');
		if ($id) {
			$data['document'] = $this->document_model->get_by_id($id);
			$data['text'] = $data['document']['extracted_text'] ?? '';
		} else {
			$data['document'] = null;
			$data['text'] = '';
		}
		$data['title'] = 'documents';
		$this->load->view('header', $data);
		$this->load->view('documents_view', $data);
	}

	public function read()
	{
		if (empty($_FILES['image']['name'])) {
			show_error('No image uploaded');
		}

		/* ===== IMAGE UPLOAD ===== */
		$config = [
			'upload_path' => FCPATH . 'uploads/documents/',
			'allowed_types' => 'jpg|jpeg|png|webp',
			'encrypt_name' => TRUE
		];

		$this->upload->initialize($config);

		if (!$this->upload->do_upload('image')) {
			show_error($this->upload->display_errors());
		}

		$upload = $this->upload->data();
		$imagePath = $upload['full_path'];

		/* ===== IMAGE CLEANING (ImageMagick) ===== */
		$cleanImage = FCPATH . 'uploads/documents/clean_' . time() . '.png';

		$magick = '"C:\xampp\htdocs\society_management\tools\imagemagick\magick.exe"';

		$cmd = $magick . ' '
			. escapeshellarg($imagePath) . ' '
			. '-resize 300% -colorspace Gray -normalize -sharpen 0x1 -threshold 60% '
			. escapeshellarg($cleanImage);

		exec($cmd . ' 2>&1');

		if (!file_exists($cleanImage)) {
			show_error('ImageMagick failed');
		}

		/* ===== OCR (Tesseract 3.02) ===== */
		$tesseract = '"C:\xampp\htdocs\society_management\tools\tesseract\tesseract.exe"';
		$outputBase = FCPATH . 'uploads/documents/ocr_' . time();

		$cmd = $tesseract . ' '
			. escapeshellarg($cleanImage) . ' '
			. escapeshellarg($outputBase) . ' '
			. '-l eng -psm 6';

		exec($cmd . ' 2>&1');

		$textFile = $outputBase . '.txt';

		if (!file_exists($textFile)) {
			show_error('OCR failed');
		}

		$text = file_get_contents($textFile);
		$text = trim(preg_replace('/\s+/', ' ', $text));

		/* ===== SAVE DOCUMENT ===== */
		$document_id = $this->document_model->insert([
			'society_id' => $this->session->userdata('society_id'),
			'document_name' => $this->input->post('document_name', TRUE),
			// 'document_type' => $this->input->post('document_type', TRUE),
			'file_path' => 'uploads/documents/' . $upload['file_name'],
			'extracted_text' => $text,
			'uploaded_by' => $this->session->userdata('user_id')
		]);

		$this->session->set_flashdata('success', 'Document uploaded and OCR completed.');
		redirect('document_controller?id=' . $document_id);
	}

	/* ===== SAVE SIGNATURE ===== */
	public function save_signature()
	{
		$base64 = $this->input->post('signature_image');
		$document_id = $this->input->post('document_id');
		$user_id = $this->session->userdata('user_name');

		if (!$base64 || !$document_id) {
			show_error('Invalid signature data');
		}

		$base64 = str_replace('data:image/png;base64,', '', $base64);
		$image = base64_decode($base64);

		$dir = FCPATH . 'uploads/signatures/';
		if (!is_dir($dir))
			mkdir($dir, 0777, true);

		$file = 'sign_' . time() . '_' . $user_id . '.png';
		file_put_contents($dir . $file, $image);

		$hash = hash_file('sha256', $dir . $file);

		$this->document_model->attach_signature([
			'id' => $document_id,
			'signature_path' => 'uploads/signatures/' . $file,
			'signature_hash' => $hash,
			'signed_by' => $user_id,
			'signed_at' => date('Y-m-d H:i:s')
		]);

		$this->session->set_flashdata('success', 'Signature saved.');
		redirect('document_controller?id=' . $document_id);
	}
}
