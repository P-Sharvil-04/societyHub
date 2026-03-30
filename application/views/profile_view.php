<?php
// $user is passed from controller
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile | Society Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f0f5fa 0%, #e9f0f5 100%);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            color: #1a2634;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
        }

        /* Header / top actions */
        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .top-actions h1 {
            font-size: 2rem;
            font-weight: 600;
            background: linear-gradient(135deg, #1e293b, #2d3b4f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            background: white;
            color: #1f2937;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .btn i {
            font-size: 1rem;
            color: #3b82f6;
        }

        .btn:hover {
            background: #f8fafd;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            border: none;
            box-shadow: 0 8px 18px -6px #3b82f6;
        }

        .btn-primary i {
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            box-shadow: 0 12px 24px -8px #2563eb;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
            border: none;
            box-shadow: 0 8px 18px -6px #f59e0b;
        }

        .btn-warning i {
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
            box-shadow: 0 12px 24px -8px #d97706;
        }

        /* Flash messages */
        .flash {
            padding: 1rem 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .flash i {
            font-size: 1.2rem;
        }

        .flash-success {
            background: #e6f7e6;
            border: 1px solid #b8e0b8;
            color: #0e6245;
        }

        .flash-error {
            background: #fff1f0;
            border: 1px solid #fccac7;
            color: #b34033;
        }

        /* Grid layout */
        .profile-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 36px;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.7);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 30px 60px -12px rgba(0, 80, 200, 0.2);
            transform: translateY(-3px);
        }

        /* Profile summary card (left) */
        .profile-summary {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .avatar-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin-bottom: 1.2rem;
            cursor: pointer;
        }

        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 20px 30px -10px rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
        }

        .avatar-upload-overlay {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #3b82f6;
            color: white;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 8px 14px -4px #1e3a8a;
            transition: all 0.2s;
            opacity: 0.9;
        }

        .avatar-wrapper:hover .avatar-upload-overlay {
            background: #2563eb;
            transform: scale(1.05);
            opacity: 1;
        }

        .avatar-wrapper:hover img {
            filter: brightness(0.95);
        }

        .profile-summary h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0.2rem 0 0.1rem;
            background: linear-gradient(145deg, #1f2937, #2d3b4f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .profile-summary .email {
            color: #5b6e8c;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .meta-item {
            background: #f0f7ff;
            border-radius: 40px;
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f4f8a;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0.25rem 0;
        }

        .meta-item i {
            color: #3b82f6;
        }

        .notice {
            background: #fff8e5;
            border: none;
            color: #b6560c;
            font-weight: 600;
            border-radius: 40px;
            padding: 0.5rem 1.2rem;
            margin-top: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        /* Forms */
        .form-section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #1e293b;
        }

        .form-section-title i {
            color: #3b82f6;
            background: #e1effe;
            padding: 0.6rem;
            border-radius: 18px;
            font-size: 1.2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #334155;
            letter-spacing: -0.2px;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 24px;
            padding: 0 0.2rem 0 1rem;
            border: 1px solid #e4eaf2;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.01);
        }

        .input-wrapper:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .input-wrapper i {
            color: #98a9b9;
            font-size: 1rem;
            width: 24px;
        }

        .input-wrapper input,
        .input-wrapper select {
            width: 100%;
            padding: 0.9rem 0.8rem 0.9rem 0.5rem;
            border: none;
            background: transparent;
            font-size: 1rem;
            outline: none;
        }

        .input-wrapper select {
            cursor: pointer;
        }

        .file-input-area {
            background: #f7fafd;
            border-radius: 30px;
            padding: 0.8rem 1.2rem;
            border: 1px dashed #bdd3e8;
            transition: background 0.2s;
        }

        .file-input-area:hover {
            background: #eaf2fa;
        }

        .file-input-area input[type="file"] {
            padding: 0.4rem 0;
            font-size: 0.9rem;
        }

        .text-muted {
            color: #6b7f99;
            font-size: 0.85rem;
            margin-top: 0.4rem;
            display: inline-block;
        }

        .action-row {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .last-updated {
            color: #6f8fae;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Password card */
        .password-card {
            margin-top: 1.8rem;
        }

        hr {
            border: none;
            height: 1px;
            background: linear-gradient(to right, #e2eaf2, transparent);
            margin: 2rem 0 1rem;
        }

        /* small adjustments */
        .mt-2 {
            margin-top: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="top-actions">
            <h1>👤 My Profile</h1>
            <a href="<?= base_url('dashboard') ?>" class="btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>

        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="flash flash-success"><i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('upload_error')): ?>
            <div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('upload_error') ?></div>
        <?php endif; ?>

        <!-- Main Grid -->
        <div class="profile-grid">
            <!-- Left Column: Profile Summary Card -->
            <div class="card profile-summary">
                <div class="avatar-wrapper">
                    <?php
                    $img = isset($user->profile_image) && $user->profile_image ? base_url('uploads/profile/' . $user->profile_image) : base_url('uploads/profile/default.png');
                    ?>
                    <img src="<?= $img ?>" alt="Profile" id="previewImage">
                    <div class="avatar-upload-overlay" onclick="document.getElementById('profile_image').click();">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <h2><?= html_escape($user->name) ?></h2>
                <div class="email"><i class="far fa-envelope" style="margin-right: 6px;"></i><?= html_escape($user->email) ?></div>
                <div class="meta-item"><i class="fas fa-door-open"></i> Flat: <?= html_escape($user->flat_no ?: '-') ?></div>
                <div class="notice"><i class="fas fa-tag"></i> Member: <?= html_escape(ucfirst($user->member_type ?: 'owner')) ?></div>
            </div>

            <!-- Right Column: Forms -->
            <div>
                <!-- Personal Information Card -->
                <div class="card">
                    <div class="form-section-title">
                        <i class="fas fa-user-edit"></i> Personal Information
                    </div>
                    <form action="<?= base_url('profile/update_profile') ?>" method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="old_profile_image" value="<?= isset($user->profile_image) ? $user->profile_image : '' ?>">

                        <div class="form-group">
                            <label for="profile_image">Profile Image</label>
                            <div class="file-input-area">
                                <input type="file" name="profile_image" id="profile_image" accept="image/*">
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> JPG, PNG up to 2MB</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input id="name" name="name" type="text" value="<?= html_escape($user->name) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input id="email" name="email" type="email" value="<?= html_escape($user->email) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-wrapper">
                                <i class="fas fa-phone-alt"></i>
                                <input id="phone" name="phone" type="text" value="<?= html_escape($user->phone) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="flat_no">Flat No.</label>
                            <div class="input-wrapper">
                                <i class="fas fa-home"></i>
                                <input id="flat_no" name="flat_no" type="text" value="<?= html_escape($user->flat_no) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="member_type">Member Type</label>
                            <div class="input-wrapper">
                                <i class="fas fa-users"></i>
                                <select id="member_type" name="member_type">
                                    <?php $types = ['owner' => 'Owner', 'tenant' => 'Tenant', 'family' => 'Family', 'staff' => 'Staff', 'other' => 'Other']; ?>
                                    <?php foreach ($types as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= (isset($user->member_type) && $user->member_type == $k) ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="action-row">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Update Profile</button>
                            <span class="last-updated"><i class="far fa-clock"></i> Last updated: <?= isset($user->updated_at) ? date('d M Y, H:i', strtotime($user->updated_at)) : '-' ?></span>
                        </div>
                    </form>
                </div>

                <!-- Change Password Card -->
                <div class="card password-card">
                    <div class="form-section-title">
                        <i class="fas fa-lock"></i> Change Password
                    </div>
                    <form action="<?= base_url('profile/change_password') ?>" method="post" novalidate>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key"></i>
                                <input id="current_password" name="current_password" type="password" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input id="new_password" name="new_password" type="password" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-check-circle"></i>
                                <input id="confirm_password" name="confirm_password" type="password" required>
                            </div>
                        </div>

                        <div class="action-row">
                            <button class="btn btn-warning" type="submit"><i class="fas fa-key"></i> Change Password</button>
                            <small class="text-muted"><i class="fas fa-shield-alt"></i> Min. 8 characters</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript: Image preview and click trigger -->
    <script>
        document.getElementById('profile_image')?.addEventListener('change', function (e) {
            const file = this.files && this.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (evt) {
                document.getElementById('previewImage').src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });

        // Optional: clicking on the camera icon triggers file input
        // Already done inline, but we can also ensure it works
        document.querySelector('.avatar-upload-overlay')?.addEventListener('click', function () {
            document.getElementById('profile_image').click();
        });
    </script>
</body>

</html>
