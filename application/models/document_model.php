<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class document_model extends CI_Model
{
	/* ===============================
	   INSERT DOCUMENT (RETURN ID)
	=============================== */
	public function insert($data)
	{
		$this->db->insert('documents', $data);
		return $this->db->insert_id();
	}

	/* ===============================
	   GET ALL DOCUMENTS
	=============================== */
	public function get_all()
	{
		return $this->db
			->order_by('id', 'DESC')
			->get('documents')
			->result_array();
	}

	/* ===============================
	   ATTACH DIGITAL SIGNATURE  FIXED
	=============================== */
	public function attach_signature($data)
	{
		return $this->db
			->where('id', $data['id'])   //  FIX HERE
			->update('documents', [
				'signature_path' => $data['signature_path'],
				'signature_hash' => $data['signature_hash'],
				'signed_by' => $data['signed_by'],
				'signed_at' => $data['signed_at']
			]);
	}

	/* ===============================
	   GET SINGLE DOCUMENT
	=============================== */
	public function get_by_id($id)
	{
		return $this->db
			->where('id', $id)
			->get('documents')
			->row_array();
	}
}
