<?php
class Expense_model extends CI_Model
{
	public function insert_expense($data)
	{
		return $this->db->insert('expenses', $data);
	}
}
?>
