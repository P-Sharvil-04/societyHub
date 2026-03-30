<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class members_controller extends CI_Controller
{
	/* ─────────────────────────────────────────────────────────────
	 * Paste these methods inside your existing feature_controller
	 * (or whichever controller handles member management).
	 * ───────────────────────────────────────────────────────────── */

	// ── LIST ─────────────────────────────────────────────────────

	public function member()
	{
		$role_name = $this->session->userdata('role_name');
		$society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		// Super admin sees ALL societies, others see only their own
		$members = $isSuperAdmin
			? $this->manage_member_model->get_all()
			: $this->manage_member_model->get_by_society($society_id);

		$totalMembers = count($members);
		$owners = 0;
		$tenants = 0;
		$committee = 0;
		$newThisMonth = 0;
		$currentMonth = date('Y-m');
		$societyGroups = [];

		foreach ($members as $m) {
			if ($m->member_type === 'owner')
				$owners++;
			if ($m->member_type === 'tenant')
				$tenants++;
			if (!empty($m->committee_role))
				$committee++;
			if (!empty($m->created_at) && substr($m->created_at, 0, 7) === $currentMonth) {
				$newThisMonth++;
			}

			if ($isSuperAdmin) {
				$sName = $m->society_name ?? 'Unknown Society';
				$societyGroups[$sName][] = $m;
			}
		}

		// ── Sort each society group: chairman always first, then by name ──
		if ($isSuperAdmin) {
			foreach ($societyGroups as $sName => &$group) {
				usort($group, function ($a, $b) {
					$aIsChairman = (strtolower($a->committee_role ?? '') === 'chairman') ? 0 : 1;
					$bIsChairman = (strtolower($b->committee_role ?? '') === 'chairman') ? 0 : 1;
					if ($aIsChairman !== $bIsChairman)
						return $aIsChairman - $bIsChairman;
					return strcmp($a->name, $b->name);
				});
			}
			unset($group);
			// Sort society groups alphabetically
			ksort($societyGroups);
		}

		$data = [
			'title' => 'Members',
			'members' => $members,
			'isSuperAdmin' => $isSuperAdmin,
			'societyGroups' => $societyGroups,
			'totalMembers' => $totalMembers,
			'owners' => $owners,
			'tenants' => $tenants,
			'committee' => $committee,
			'newThisMonth' => $newThisMonth,
			'ownerPercent' => $totalMembers > 0 ? round(($owners / $totalMembers) * 100) : 0,
			'tenantPercent' => $totalMembers > 0 ? round(($tenants / $totalMembers) * 100) : 0,
			'committee_roles' => $this->manage_member_model->get_roles(),
		];

		$this->load->view('header', $data);
		$this->load->view('manage_member_view', $data);
	}

	// ── ADD / EDIT ────────────────────────────────────────────────
	public function save()
	{
		$this->load->library('email');

		$society_id = $this->session->userdata('society_id');
		$memberId = $this->input->post('memberId');

		$this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
		$this->form_validation->set_rules('flat_no', 'Flat Number', 'required|trim');
		$this->form_validation->set_rules('phone', 'Phone', 'required|numeric|exact_length[10]');
		$this->form_validation->set_rules('member_type', 'Member Type', 'required|in_list[owner,tenant]');

		if (empty($memberId)) {
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
		}

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('manage_member');
		}

		$firstName = $this->input->post('first_name', TRUE);
		$lastName = $this->input->post('last_name', TRUE);
		$flatNo = $this->input->post('flat_no', TRUE);
		$wingId = $this->input->post('wing_id', TRUE) ?: null;
		$email = $this->input->post('email', TRUE) ?: null;
		$phone = $this->input->post('phone', TRUE);
		$memberType = $this->input->post('member_type', TRUE);
		$statusRaw = $this->input->post('status', TRUE);
		$status = ($statusRaw === '1' || $statusRaw === 'Active') ? 1 : 0;
		$password = $this->input->post('password', FALSE);

		$memberData = [
			'name' => trim($firstName . ' ' . $lastName),
			'flat_no' => $flatNo,
			'wing_id' => $wingId,
			'phone' => $phone,
			'email' => $email,
			'member_type' => $memberType,
			'status' => $status,
			'society_id' => $society_id,
		];

		if (!empty($password)) {
			$memberData['password'] = password_hash($password, PASSWORD_DEFAULT);
		}

		if (!empty($memberId)) {
			// UPDATE
			if ($this->manage_member_model->memberExists($phone, $email, $society_id, $memberId)) {
				$this->session->set_flashdata('error', 'Phone or email already in use by another member.');
				redirect('manage_member');
			}
			if ($memberType === 'owner' && $this->manage_member_model->ownerExistsForFlat($flatNo, $society_id, $memberId)) {
				$this->session->set_flashdata('error', 'An owner already exists for this flat.');
				redirect('manage_member');
			}
			$this->manage_member_model->update_member($memberId, $memberData);
			$this->session->set_flashdata('success', 'Member updated successfully.');
			redirect('manage_member');
		}

		// INSERT
		if ($this->manage_member_model->memberExists($phone, $email, $society_id)) {
			$this->session->set_flashdata('error', 'A member with this phone or email already exists.');
			redirect('manage_member');
		}
		if ($memberType === 'owner' && $this->manage_member_model->ownerExistsForFlat($flatNo, $society_id)) {
			$this->session->set_flashdata('error', 'An owner already exists for this flat.');
			redirect('manage_member');
		}

		$insertId = $this->manage_member_model->create($memberData);
		if ($insertId && !empty($email)) {
			$this->_sendWelcomeEmail(['name' => $memberData['name'], 'email' => $email, 'flat_no' => $flatNo], $password);
		}

		$this->session->set_flashdata(
			'success',
			'Member added successfully!' . (!empty($email) ? ' Welcome email sent.' : '')
		);
		redirect('manage_member');
	}

	// ── DELETE ────────────────────────────────────────────────────
	public function delete_member($id)
	{
		if (empty($id)) {
			$this->session->set_flashdata('error', 'Invalid member ID.');
			redirect('manage_member');
		}
		if ($this->manage_member_model->delete($id)) {
			$this->session->set_flashdata('success', 'Member deleted successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to delete member.');
		}
		redirect('manage_member');
	}

	// ── ASSIGN COMMITTEE ROLE ─────────────────────────────────────
	public function assign_role()
	{
		$user_id = $this->input->post('committeeMemberId', TRUE);
		$role_id = $this->input->post('committeeRole', TRUE);

		if (!$user_id || !$role_id) {
			$this->session->set_flashdata('error', 'Please select both a member and a role.');
			redirect('manage_member');
		}

		if (!in_array((int) $role_id, $this->manage_member_model::COMMITTEE_ROLE_IDS)) {
			$this->session->set_flashdata('error', 'Invalid role selected.');
			redirect('manage_member');
		}

		if ($this->manage_member_model->assign_committee_role($user_id, (int) $role_id)) {
			$this->session->set_flashdata('success', 'Committee role assigned successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to assign committee role.');
		}
		redirect('manage_member');
	}

	// ── REMOVE COMMITTEE ROLE ─────────────────────────────────────
	public function remove_role()
	{
		$id = $this->input->post('id', TRUE);
		if (!$id) {
			$this->session->set_flashdata('error', 'Invalid request.');
			redirect('manage_member');
		}
		if ($this->manage_member_model->remove_committee_role($id)) {
			$this->session->set_flashdata('success', 'Committee role removed.');
		} else {
			$this->session->set_flashdata('error', 'Failed to remove role.');
		}
		redirect('manage_member');
	}
	private function _sendWelcomeEmail($member, $plainPassword)
	{
		$this->email->from('sharvil.tmbs25@gmail.com', 'Society Management');
		$this->email->to($member['email']);
		$this->email->subject('Welcome to Society Management');
		$this->email->attach(FCPATH . 'assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png', 'inline');
		$cid = $this->email->attachment_cid(FCPATH . 'assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png');

		$message = "
				<center>
				<img src='cid:$cid' width='120'>
				</center>

				<h3>Welcome {$member['name']}!</h3>
				<p>You have been successfully added as a society member.</p>

				<p><strong>Your Login Details:</strong></p>
				<p>
				Email: {$member['email']}<br>
				Password: {$plainPassword}
				</p>

				<p>Flat No: {$member['flat_no']}</p>

				<br>
				<p>Regards,<br>Society Management Team</p>
				";

		$this->email->set_mailtype('html');
		$this->email->message($message);

		if (!$this->email->send()) {
			log_message('error', $this->email->print_debugger());
		}
	}
}
