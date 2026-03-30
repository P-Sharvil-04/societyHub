<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class aminity_controller extends CI_Controller
{
	private $xml_path;

	public function __construct()
	{
		parent::__construct();
		$this->xml_path = APPPATH . 'config/xml/amenities.xml';
		header('Content-Type: application/json');
	}

	// main page (loads view)
	public function index()
	{
		$this->load->view('amenities_view');
	}

	// returns JSON list
	public function list()
	{
		$data = $this->read_xml();
		echo json_encode(['success' => true, 'data' => $data]);
	}

	// create or update (POST). Send form fields; include id to update.
	public function save()
	{
		$post = $this->input->post();
		if (!$post) {
			echo json_encode(['success' => false, 'message' => 'No data']);
			return;
		}

		$xml = simplexml_load_file($this->xml_path);
		if (!$xml) {
			echo json_encode(['success' => false, 'message' => 'Cannot load XML']);
			return;
		}

		$id = isset($post['id']) && $post['id'] !== '' ? (int) $post['id'] : null;

		if ($id) {
			// update existing
			foreach ($xml->amenity as $amen) {
				if ((int) $amen['id'] === $id) {
					foreach ($post as $k => $v) {
						if ($k === 'id')
							continue;
						// ensure element exists or add
						if (isset($amen->$k))
							$amen->$k = $v;
						else
							$amen->addChild($k, $v);
					}
					break;
				}
			}
			$message = 'Amenity updated';
		} else {
			// create new
			$ids = array_map(function ($a) {
				return (int) $a['id']; }, iterator_to_array($xml->amenity));
			$newId = $ids ? max($ids) + 1 : 1;
			$amen = $xml->addChild('amenity');
			$amen->addAttribute('id', $newId);

			// generate amenityId like AMN-2026-001
			$year = date('Y');
			$count = count($xml->amenity);
			$amen->addChild('amenityId', "AMN-{$year}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT));

			$fields = ['name', 'category', 'icon', 'capacity', 'price', 'location', 'status', 'openingTime', 'closingTime', 'description'];
			foreach ($fields as $f) {
				$amen->addChild($f, isset($post[$f]) ? $post[$f] : '');
			}
			$message = 'Amenity added';
		}

		// save with exclusive lock
		$saved = file_put_contents($this->xml_path, $xml->asXML(), LOCK_EX);
		if ($saved === false) {
			echo json_encode(['success' => false, 'message' => 'Failed to save XML']);
			return;
		}

		echo json_encode(['success' => true, 'message' => $message]);
	}

	// delete via POST id
	public function delete()
	{
		$id = $this->input->post('id');
		if (!$id) {
			echo json_encode(['success' => false, 'message' => 'Missing id']);
			return;
		}

		$xml = simplexml_load_file($this->xml_path);
		$idx = 0;
		$found = false;
		foreach ($xml->amenity as $amen) {
			if ((int) $amen['id'] === (int) $id) {
				unset($xml->amenity[$idx]);
				$found = true;
				break;
			}
			$idx++;
		}

		if (!$found) {
			echo json_encode(['success' => false, 'message' => 'Not found']);
			return;
		}

		$saved = file_put_contents($this->xml_path, $xml->asXML(), LOCK_EX);
		if ($saved === false) {
			echo json_encode(['success' => false, 'message' => 'Failed to save XML']);
			return;
		}

		echo json_encode(['success' => true, 'message' => 'Amenity deleted']);
	}

	// helper: read XML and return array
	private function read_xml()
	{
		if (!file_exists($this->xml_path))
			return [];
		$xml = simplexml_load_file($this->xml_path);
		$result = [];
		foreach ($xml->amenity as $a) {
			$obj = ['id' => (int) $a['id']];
			foreach ($a as $k => $v)
				$obj[$k] = (string) $v;
			$result[] = $obj;
		}
		return $result;
	}
}
