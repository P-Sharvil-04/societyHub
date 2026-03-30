<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model
{
	protected $table = 'payments';

	public function __construct()
	{
		parent::__construct();
	}

	public function insert_payment($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function get_all_orders()
	{
		$this->db->order_by('id', 'desc');
		return $this->db->get($this->table)->result_array();
	}

	public function get_order_by_orderid($order_id)
	{
		return $this->db->get_where($this->table, ['order_id' => $order_id])->row_array();
	}

	public function mark_order_paid($order_id, $razorpay_payment_id)
	{
		$this->db->where('order_id', $order_id);
		$this->db->update($this->table, [
			'status' => 'paid',
			'payment_id' => $razorpay_payment_id,
			'paid_at' => date('Y-m-d H:i:s')
		]);
		return $this->db->affected_rows();
	}

	/**
	 * Return sum of paid orders (decimal), used as simulated balance in test mode.
	 * NOTE: this sums only DB records marked 'paid'.
	 */
	public function get_total_paid_amount()
	{
		$this->db->select('COALESCE(SUM(amount),0) as total');
		$this->db->where('status', 'paid');
		$row = $this->db->get($this->table)->row_array();
		return isset($row['total']) ? (float) $row['total'] : 0.0;
	}
}
