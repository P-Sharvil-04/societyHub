<?php defined('BASEPATH') OR exit('No direct script access allowed');
$s = $settings ?? [];
function sv($k,$s,$d=''){ return htmlspecialchars($s[$k]??$d,ENT_QUOTES,'UTF-8'); }
function sc($k,$s){ return (!empty($s[$k]) && $s[$k]!=='0') ? 'checked' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover">
    <title>SocietyHub · Settings</title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <style>
        /* Tab bar */
        .stab-bar{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:28px;background:var(--card-bg);border:1px solid var(--border);border-radius:14px;padding:6px;width:fit-content;}
        .stab-btn{padding:9px 20px;font-size:.88rem;font-weight:600;color:var(--text-light);background:transparent;border:none;cursor:pointer;border-radius:10px;transition:.15s;display:flex;align-items:center;gap:7px;white-space:nowrap;text-decoration:none;}
        .stab-btn.active{background:var(--primary);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,.3);}
        .stab-btn:not(.active):hover{background:var(--bg-light,#f5f5f5);color:var(--primary);}
        .stab-panel{display:none;}.stab-panel.active{display:block;}
        /* Settings grid */
        .sg{display:grid;grid-template-columns:1fr;gap:22px;}
        @media(min-width:800px){.sg{grid-template-columns:repeat(2,1fr);}}
        /* Card */
        .sc{background:var(--card-bg);border-radius:20px;border:1px solid var(--border);padding:24px;height:fit-content;transition:.2s;}
        .sc:hover{box-shadow:0 4px 20px rgba(0,0,0,.06);}
        .sc h2{font-size:1.05rem;font-weight:700;color:var(--text-dark);display:flex;align-items:center;gap:9px;padding-bottom:14px;border-bottom:1px solid var(--border);margin-bottom:18px;}
        .sc h2 .hico{color:var(--primary);width:28px;height:28px;background:rgba(99,102,241,.1);border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;}
        .sc-full{grid-column:1/-1;}
        /* Toggle row */
        .tr{display:flex;align-items:center;justify-content:space-between;background:var(--light-bg,#f8f9fa);padding:11px 14px;border-radius:11px;border:1px solid var(--border);margin-bottom:10px;gap:10px;}
        .tr:last-child{margin-bottom:0;}
        .tr .tl{font-size:.88rem;font-weight:500;color:var(--text-dark);}
        .tr .ts{font-size:.75rem;color:var(--text-light);margin-top:2px;}
        /* Toggle switch CSS-only */
        .tsw{position:relative;display:inline-block;width:44px;height:22px;flex-shrink:0;}
        .tsw input{opacity:0;width:0;height:0;}
        .tsl{position:absolute;cursor:pointer;inset:0;background:var(--border,#d1d5db);border-radius:22px;transition:.2s;}
        .tsl:before{content:'';position:absolute;width:16px;height:16px;background:#fff;border-radius:50%;left:3px;top:3px;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
        .tsw input:checked+.tsl{background:var(--primary);}
        .tsw input:checked+.tsl:before{transform:translateX(22px);}
        /* API field */
        .af{position:relative;}
        .af .form-control{padding-right:44px;font-family:monospace;font-size:.85rem;}
        .af .eb{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-light);cursor:pointer;font-size:1rem;}
        .af .eb:hover{color:var(--primary);}
        /* Profile header */
        .ph{display:flex;align-items:center;gap:18px;padding:16px;background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(139,92,246,.06));border-radius:14px;margin-bottom:20px;border:1px solid rgba(99,102,241,.15);}
        .pa{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark,#4f46e5));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;font-weight:700;flex-shrink:0;}
        .rp{display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:capitalize;margin-top:5px;}
        .rp.super_admin{background:#f0f5ff;color:#2f54eb;}.rp.chairman{background:#fff7e6;color:#d48806;}
        .rp.owner{background:#f6ffed;color:#389e0d;}.rp.secretary{background:#fff0f6;color:#c41d7f;}
        /* Mini stats */
        .ms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:20px;}
        .ms{background:var(--light-bg,#f8f9fa);border-radius:12px;padding:14px;border:1px solid var(--border);text-align:center;}
        .ms .v{font-size:1.6rem;font-weight:800;color:var(--primary);}
        .ms .l{font-size:.73rem;color:var(--text-light);margin-top:3px;}
        /* Save bar */
        .svbar{position:sticky;bottom:0;background:var(--card-bg);border-top:1px solid var(--border);padding:12px 20px;display:flex;justify-content:flex-end;gap:10px;margin:20px -24px -24px;border-radius:0 0 20px 20px;}
        /* Password strength */
        .pw-bar{height:4px;border-radius:2px;margin-top:6px;background:#e5e7eb;transition:all .3s;}
        .pw-bar.weak{background:#f5222d;width:33%;}.pw-bar.medium{background:#faad14;width:66%;}.pw-bar.strong{background:#52c41a;width:100%;}
        .pw-hint{font-size:.72rem;margin-top:3px;}
        .pw-hint.weak{color:#f5222d;}.pw-hint.medium{color:#faad14;}.pw-hint.strong{color:#52c41a;}
        /* 2-col form row */
        .fr2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        @media(max-width:600px){.fr2{grid-template-columns:1fr;}.sc{padding:16px;}}
        /* Society table */
        .soc-status{display:inline-block;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:600;}
        .soc-status.active{background:#f6ffed;color:#389e0d;}.soc-status.inactive{background:#fff1f0;color:#cf1322;}
        /* Info rows */
        .info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--light-bg,#f8f9fa);border-radius:10px;border:1px solid var(--border);margin-bottom:8px;}
        .info-row:last-child{margin-bottom:0;}
        .info-row .ik{font-size:.84rem;color:var(--text-light);}
        .info-row .iv{font-size:.84rem;font-weight:600;color:var(--text-dark);}
    </style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<?php $activePage = 'settings'; include('sidebar.php'); ?>

<div class="main" id="main">

    <?php if ($this->session->flashdata('success')): ?>
        <div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <!-- Page heading -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;font-weight:800;font-size:1.4rem;display:flex;align-items:center;gap:10px;">
                <span style="background:rgba(99,102,241,.1);padding:8px 10px;border-radius:10px;"><i class="fas fa-cogs" style="color:var(--primary)"></i></span>
                Settings
            </h2>
            <p style="margin:4px 0 0;color:var(--text-light);font-size:.84rem;">
                <?php if ($isSuperAdmin): ?>Global system &amp; all-societies configuration
                <?php elseif ($isChairman): ?>Society management &amp; configuration
                <?php else: ?>Profile &amp; personal preferences
                <?php endif; ?>
            </p>
        </div>
        <span class="rp <?= html_escape($role??'') ?>"><?= ucwords(str_replace('_',' ',$role??'')) ?></span>
    </div>

    <!-- ══ TAB BAR ══ -->
    <div class="stab-bar">
        <button class="stab-btn active" onclick="switchTab('profile',this)"><i class="fas fa-user-circle"></i> My Profile</button>
        <?php if (!$isOwner): ?>
            <?php if (!$isSuperAdmin): ?>
                <button class="stab-btn" onclick="switchTab('society',this)"><i class="fas fa-building"></i> Society</button>
                <button class="stab-btn" onclick="switchTab('maintenance',this)"><i class="fas fa-tools"></i> Maintenance</button>
            <?php endif; ?>
            <button class="stab-btn" onclick="switchTab('payment',this)"><i class="fas fa-credit-card"></i> Payment</button>
        <?php endif; ?>
        <button class="stab-btn" onclick="switchTab('notifications',this)"><i class="fas fa-bell"></i> Notifications</button>
        <button class="stab-btn" onclick="switchTab('security',this)"><i class="fas fa-shield-alt"></i> Security</button>
        <?php if ($isSuperAdmin): ?>
            <button class="stab-btn" onclick="switchTab('societies',this)"><i class="fas fa-city"></i> Societies</button>
            <button class="stab-btn" onclick="switchTab('system',this)"><i class="fas fa-server"></i> System</button>
        <?php endif; ?>
    </div>

    <!-- ══════════ TAB: PROFILE (all roles) ══════════ -->
    <div class="stab-panel active" id="tab-profile">
        <form method="POST" action="<?= base_url('settings_controller/save_profile') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <!-- Personal Info -->
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-id-card"></i></span> Personal Information</h2>
                    <div class="ph">
                        <div class="pa"><?= strtoupper(substr($user['name']??'U',0,1)) ?></div>
                        <div>
                            <div style="font-size:1rem;font-weight:700;color:var(--text-dark);"><?= html_escape($user['name']??'') ?></div>
                            <div style="font-size:.8rem;color:var(--text-light);"><?= html_escape($user['email']??'') ?></div>
                            <span class="rp <?= html_escape($role??'') ?>"><?= ucwords(str_replace('_',' ',$role??'')) ?></span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= html_escape($user['name']??'') ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?= html_escape($user['email']??'') ?>" required>
                    </div>
                    <div class="fr2">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= html_escape($user['phone']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label>Flat / Unit</label>
                            <input type="text" class="form-control" value="<?= html_escape($user['flat_no']??'—') ?>" readonly style="background:var(--bg,#f5f5f5);cursor:not-allowed;">
                        </div>
                    </div>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Profile</button>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-key"></i></span> Change Password</h2>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Current Password</label>
                        <div class="af">
                            <input type="password" name="current_password" id="currPw" class="form-control" placeholder="Enter current password">
                            <button type="button" class="eb" onclick="togglePw('currPw','eCurr')"><i id="eCurr" class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:6px;">
                        <label>New Password</label>
                        <div class="af">
                            <input type="password" name="new_password" id="newPw" class="form-control" placeholder="Min 6 characters" oninput="pwStrength(this.value)">
                            <button type="button" class="eb" onclick="togglePw('newPw','eNew')"><i id="eNew" class="fas fa-eye"></i></button>
                        </div>
                        <div class="pw-bar" id="pwBar"></div>
                        <div class="pw-hint" id="pwHint"></div>
                    </div>
                    <div class="form-group" style="margin-bottom:18px;">
                        <label>Confirm New Password</label>
                        <div class="af">
                            <input type="password" name="confirm_password" id="confPw" class="form-control" placeholder="Re-enter new password">
                            <button type="button" class="eb" onclick="togglePw('confPw','eConf')"><i id="eConf" class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div style="background:#fffbe6;border:1px solid #ffe58f;border-radius:10px;padding:11px 14px;font-size:.81rem;color:#7c5319;">
                        <i class="fas fa-info-circle" style="color:#d48806;"></i> Leave password fields blank to keep your current password.
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ══════════ TAB: SOCIETY (chairman only) ══════════ -->
    <?php if (!$isSuperAdmin && !$isOwner): ?>
    <div class="stab-panel" id="tab-society">
        <form method="POST" action="<?= base_url('settings_controller/save_society') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <div class="sc sc-full">
                    <h2><span class="hico"><i class="fas fa-building"></i></span> Society Information</h2>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Society Name</label>
                        <input type="text" name="society_name" class="form-control" value="<?= html_escape($society_row['name']??'') ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Address</label>
                        <textarea name="society_address" class="form-control" rows="2"><?= html_escape($society_row['address']??'') ?></textarea>
                    </div>
                    <div class="fr2">
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="tel" name="society_phone" class="form-control" value="<?= html_escape($society_row['phone']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="society_email" class="form-control" value="<?= html_escape($society_row['email']??'') ?>">
                        </div>
                    </div>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Society Info</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ══════════ TAB: MAINTENANCE (chairman only) ══════════ -->
    <div class="stab-panel" id="tab-maintenance">
        <form method="POST" action="<?= base_url('settings_controller/save_society') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-tools"></i></span> Maintenance Fee</h2>
                    <div class="fr2" style="margin-bottom:14px;">
                        <div class="form-group">
                            <label>Due Day (of month)</label>
                            <input type="number" name="maintenance_due_date" class="form-control" min="1" max="31" value="<?= sv('maintenance_due_date',$s,'10') ?>">
                        </div>
                        <div class="form-group">
                            <label>Late Fee (₹)</label>
                            <input type="number" name="maintenance_late_fee" class="form-control" min="0" value="<?= sv('maintenance_late_fee',$s,'100') ?>">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Interest Rate (% per month)</label>
                        <input type="number" step="0.1" name="interest_rate" class="form-control" value="<?= sv('interest_rate',$s,'2.5') ?>">
                    </div>
                    <div class="tr">
                        <div><div class="tl">Automatic payment reminders</div><div class="ts">Send reminder 3 days before due date</div></div>
                        <label class="tsw"><input type="checkbox" name="auto_reminders" value="1" <?= sc('auto_reminders',$s) ?: 'checked' ?>><span class="tsl"></span></label>
                    </div>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    </div>
                </div>
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-lightbulb"></i></span> Tips</h2>
                    <div style="font-size:.84rem;color:var(--text-light);line-height:2;">
                        <p><i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>Set due date to match your billing cycle.</p>
                        <p><i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>Late fee is charged per occurrence after due date.</p>
                        <p><i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>Interest compounds monthly on overdue amounts.</p>
                        <p><i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>Auto-reminders go out 3 days before the due date.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ══════════ TAB: PAYMENT (super admin + chairman) ══════════ -->
    <?php if (!$isOwner): ?>
    <div class="stab-panel" id="tab-payment">
        <?php
        $saveAction  = $isSuperAdmin ? 'save_system' : 'save_society';
        $keyField    = $isSuperAdmin ? 'global_razorpay_key_id'     : 'razorpay_key_id';
        $secretField = $isSuperAdmin ? 'global_razorpay_key_secret'  : 'razorpay_key_secret';
        $testField   = $isSuperAdmin ? 'global_test_mode'            : 'razorpay_test_mode';
        ?>
        <form method="POST" action="<?= base_url('settings_controller/'.$saveAction) ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-credit-card"></i></span> Razorpay Configuration</h2>
                    <div style="background:#f0f5ff;border:1px solid #d6e4ff;border-radius:10px;padding:11px 14px;font-size:.81rem;color:#1d39c4;margin-bottom:16px;">
                        <i class="fas fa-info-circle"></i> Get keys from <a href="https://dashboard.razorpay.com" target="_blank" style="color:#1d39c4;font-weight:600;">dashboard.razorpay.com</a> → Settings → API Keys
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Key ID <small style="color:var(--text-light);">(<?= $isSuperAdmin ? 'Global' : 'Society' ?>)</small></label>
                        <div class="af">
                            <input type="password" name="<?= $keyField ?>" id="rzpK" class="form-control" value="<?= sv($keyField,$s,'') ?>" placeholder="rzp_test_...">
                            <button type="button" class="eb" onclick="togglePw('rzpK','eRzpK')"><i id="eRzpK" class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Key Secret <small style="color:var(--text-light);">(<?= $isSuperAdmin ? 'Global' : 'Society' ?>)</small></label>
                        <div class="af">
                            <input type="password" name="<?= $secretField ?>" id="rzpS" class="form-control" value="<?= sv($secretField,$s,'') ?>" placeholder="Your secret key">
                            <button type="button" class="eb" onclick="togglePw('rzpS','eRzpS')"><i id="eRzpS" class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="tr">
                        <div><div class="tl">Test Mode</div><div class="ts">Use test keys — no real transactions</div></div>
                        <label class="tsw"><input type="checkbox" name="<?= $testField ?>" value="1" <?= sc($testField,$s) ?: 'checked' ?>><span class="tsl"></span></label>
                    </div>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Payment Settings</button>
                    </div>
                </div>
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-lock"></i></span> Payment Security</h2>
                    <div style="font-size:.84rem;color:var(--text-light);line-height:2;">
                        <p><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:6px;"></i>Keys are stored in the database, never in code.</p>
                        <p><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:6px;"></i>Each payment is verified via HMAC-SHA256 signature before being marked paid.</p>
                        <p><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:6px;"></i>Test mode uses <code>rzp_test_</code> keys — safe for development.</p>
                        <p><i class="fas fa-shield-alt" style="color:var(--primary);margin-right:6px;"></i>Never share your Key Secret with anyone.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ══════════ TAB: NOTIFICATIONS (all roles) ══════════ -->
    <div class="stab-panel" id="tab-notifications">
        <?php $notifAction = $isSuperAdmin ? 'save_system' : ($isOwner ? 'save_owner' : 'save_society'); ?>
        <form method="POST" action="<?= base_url('settings_controller/'.$notifAction) ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-bell"></i></span> Notification Channels</h2>
                    <div class="tr">
                        <div><div class="tl"><i class="fas fa-envelope" style="color:#6366f1;margin-right:6px;"></i>Email Notifications</div><div class="ts">Updates &amp; alerts via email</div></div>
                        <label class="tsw"><input type="checkbox" name="email_notif" value="1" <?= sc('email_notif',$s) ?: 'checked' ?>><span class="tsl"></span></label>
                    </div>
                    <div class="tr">
                        <div><div class="tl"><i class="fas fa-sms" style="color:#6366f1;margin-right:6px;"></i>SMS Alerts</div><div class="ts">Critical updates via SMS</div></div>
                        <label class="tsw"><input type="checkbox" name="sms_notif" value="1" <?= sc('sms_notif',$s) ?>><span class="tsl"></span></label>
                    </div>
                    <div class="tr">
                        <div><div class="tl"><i class="fas fa-mobile-alt" style="color:#6366f1;margin-right:6px;"></i>Push Notifications</div><div class="ts">Mobile app notifications</div></div>
                        <label class="tsw"><input type="checkbox" name="push_notif" value="1" <?= sc('push_notif',$s) ?: 'checked' ?>><span class="tsl"></span></label>
                    </div>
                    <?php if (!$isOwner): ?>
                    <div class="form-group" style="margin-top:14px;">
                        <label>Alert Email Address</label>
                        <input type="email" name="notif_email" class="form-control" value="<?= sv('notif_email',$s,'') ?>" placeholder="alerts@yoursociety.com">
                    </div>
                    <?php endif; ?>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Preferences</button>
                    </div>
                </div>
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-list-check"></i></span> You Will Receive</h2>
                    <div style="font-size:.84rem;color:var(--text-light);line-height:2;">
                        <p><i class="fas fa-dot-circle" style="color:#6366f1;font-size:.7rem;margin-right:8px;vertical-align:middle;"></i>Maintenance due date reminders</p>
                        <p><i class="fas fa-dot-circle" style="color:#6366f1;font-size:.7rem;margin-right:8px;vertical-align:middle;"></i>Booking approval / rejection updates</p>
                        <p><i class="fas fa-dot-circle" style="color:#6366f1;font-size:.7rem;margin-right:8px;vertical-align:middle;"></i>New event &amp; festival announcements</p>
                        <p><i class="fas fa-dot-circle" style="color:#6366f1;font-size:.7rem;margin-right:8px;vertical-align:middle;"></i>Complaint status changes</p>
                        <p><i class="fas fa-dot-circle" style="color:#6366f1;font-size:.7rem;margin-right:8px;vertical-align:middle;"></i>Notice board updates</p>
                        <?php if (!$isOwner): ?><p><i class="fas fa-dot-circle" style="color:#6366f1;font-size:.7rem;margin-right:8px;vertical-align:middle;"></i>Payment receipts &amp; overdue alerts</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ══════════ TAB: SECURITY (all roles) ══════════ -->
    <div class="stab-panel" id="tab-security">
        <?php $secAction = $isSuperAdmin ? 'save_system' : ($isOwner ? 'save_owner' : 'save_society'); ?>
        <form method="POST" action="<?= base_url('settings_controller/'.$secAction) ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-shield-alt"></i></span> Security Settings</h2>
                    <div class="tr" style="margin-bottom:14px;">
                        <div><div class="tl">Two-Factor Authentication (2FA)</div><div class="ts">Extra verification step on login</div></div>
                        <label class="tsw"><input type="checkbox" name="two_factor" value="1" <?= sc('two_factor',$s) ?>><span class="tsl"></span></label>
                    </div>
                    <div class="fr2">
                        <div class="form-group">
                            <label>Session Timeout (minutes)</label>
                            <input type="number" name="session_timeout<?= $isSuperAdmin?'_global':'' ?>" class="form-control" min="5" max="480" value="<?= sv($isSuperAdmin?'session_timeout_global':'session_timeout',$s,'30') ?>">
                        </div>
                        <div class="form-group">
                            <label>Password Expiry (days)</label>
                            <input type="number" name="password_expiry<?= $isSuperAdmin?'_global':'' ?>" class="form-control" min="0" value="<?= sv($isSuperAdmin?'password_expiry_global':'password_expiry',$s,'90') ?>">
                            <small style="color:var(--text-light);font-size:.73rem;">0 = never expires</small>
                        </div>
                    </div>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Security Settings</button>
                    </div>
                </div>
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-info-circle"></i></span> Security Tips</h2>
                    <div style="font-size:.84rem;color:var(--text-light);line-height:2;">
                        <p><i class="fas fa-check-circle" style="color:#52c41a;margin-right:6px;"></i>Enable 2FA for extra protection.</p>
                        <p><i class="fas fa-check-circle" style="color:#52c41a;margin-right:6px;"></i>Use 30-min session timeout on shared computers.</p>
                        <p><i class="fas fa-check-circle" style="color:#52c41a;margin-right:6px;"></i>Rotate passwords every 90 days.</p>
                        <p><i class="fas fa-check-circle" style="color:#52c41a;margin-right:6px;"></i>Use 8+ characters with letters, numbers &amp; symbols.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ══════════ TAB: SOCIETIES LIST (super admin) ══════════ -->
    <?php if ($isSuperAdmin): ?>
    <div class="stab-panel" id="tab-societies">
        <?php if (!empty($system_info)): ?>
        <div class="ms-grid">
            <div class="ms"><div class="v"><?= $system_info['total_societies'] ?></div><div class="l">Societies</div></div>
            <div class="ms"><div class="v"><?= $system_info['total_users']     ?></div><div class="l">Users</div></div>
            <div class="ms"><div class="v"><?= $system_info['total_staff']     ?></div><div class="l">Staff</div></div>
            <div class="ms"><div class="v"><?= $system_info['total_bookings']  ?></div><div class="l">Bookings</div></div>
            <div class="ms"><div class="v"><?= $system_info['total_complaints']?></div><div class="l">Complaints</div></div>
        </div>
        <?php endif; ?>
        <div class="sc">
            <h2><span class="hico"><i class="fas fa-city"></i></span> All Registered Societies</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>Society</th><th>Email</th><th>Phone</th><th>Address</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_societies)): ?>
                            <?php foreach ($all_societies as $i => $soc): ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><strong><?= html_escape($soc['name']) ?></strong></td>
                                    <td><?= html_escape($soc['email']??'—') ?></td>
                                    <td><?= html_escape($soc['phone']??'—') ?></td>
                                    <td style="font-size:.8rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= html_escape($soc['address']??'—') ?></td>
                                    <td><span class="soc-status <?= html_escape($soc['status']??'active') ?>"><?= ucfirst($soc['status']??'active') ?></span></td>
                                    <td>
                                        <form method="POST" action="<?= base_url('settings_controller/toggle_society/'.(int)$soc['id']) ?>" style="display:inline;">
                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                            <button type="submit" class="btn-icon <?= ($soc['status']??'active')==='active'?'delete':'' ?>"
                                                    title="<?= ($soc['status']??'active')==='active'?'Deactivate':'Activate' ?>"
                                                    onclick="return confirm('Toggle status for <?= html_escape(addslashes($soc['name'])) ?>?')">
                                                <i class="fas <?= ($soc['status']??'active')==='active'?'fa-toggle-on':'fa-toggle-off' ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-light);">No societies found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ══════════ TAB: SYSTEM (super admin) ══════════ -->
    <div class="stab-panel" id="tab-system">
        <form method="POST" action="<?= base_url('settings_controller/save_system') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="sg">
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-server"></i></span> System Configuration</h2>
                    <div class="fr2" style="margin-bottom:14px;">
                        <div class="form-group">
                            <label>Timezone</label>
                            <select name="system_timezone" class="form-control">
                                <?php $tz = sv('system_timezone',$s,'Asia/Kolkata');
                                foreach (['Asia/Kolkata','Asia/Dubai','Asia/Singapore','Europe/London','America/New_York'] as $t): ?>
                                    <option value="<?= $t ?>" <?= $tz===$t?'selected':'' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Environment</label>
                            <select name="system_env" class="form-control">
                                <?php $env = sv('system_env',$s,'production'); ?>
                                <option value="development" <?= $env==='development'?'selected':'' ?>>Development</option>
                                <option value="staging"     <?= $env==='staging'?'selected':'' ?>>Staging</option>
                                <option value="production"  <?= $env==='production'?'selected':'' ?>>Production</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Default Role for New Residents</label>
                        <select name="default_role" class="form-control">
                            <?php $dr = sv('default_role',$s,'owner'); ?>
                            <option value="owner"  <?= $dr==='owner'?'selected':'' ?>>Owner</option>
                            <option value="tenant" <?= $dr==='tenant'?'selected':'' ?>>Tenant</option>
                        </select>
                    </div>
                    <div class="tr" style="margin-bottom:10px;">
                        <div><div class="tl">Allow residents to add guests</div></div>
                        <label class="tsw"><input type="checkbox" name="allow_guests" value="1" <?= sc('allow_guests',$s)?:'checked' ?>><span class="tsl"></span></label>
                    </div>
                    <div class="tr">
                        <div><div class="tl">Enable committee access panel</div></div>
                        <label class="tsw"><input type="checkbox" name="committee_access" value="1" <?= sc('committee_access',$s)?:'checked' ?>><span class="tsl"></span></label>
                    </div>
                    <div class="svbar">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save System Settings</button>
                    </div>
                </div>
                <div class="sc">
                    <h2><span class="hico"><i class="fas fa-info-circle"></i></span> System Information</h2>
                    <div class="info-row"><span class="ik">PHP Version</span><span class="iv"><?= PHP_VERSION ?></span></div>
                    <div class="info-row"><span class="ik">CodeIgniter</span><span class="iv"><?= CI_VERSION ?></span></div>
                    <div class="info-row"><span class="ik">Database</span><span class="iv"><?= $this->db->platform() ?> <?= $this->db->version() ?></span></div>
                    <div class="info-row"><span class="ik">Server OS</span><span class="iv"><?= php_uname('s').' '.php_uname('r') ?></span></div>
                    <div class="info-row"><span class="ik">Current Timezone</span><span class="iv"><?= sv('system_timezone',$s,'Asia/Kolkata') ?></span></div>
                    <div class="info-row"><span class="ik">Environment</span><span class="iv"><?= ucfirst(sv('system_env',$s,'production')) ?></span></div>
                    <div class="info-row"><span class="ik">Last Saved</span><span class="iv"><?= date('d M Y, h:i A') ?></span></div>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

</div><!-- /.main -->

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
/* Tab switch */
function switchTab(name, btn) {
    document.querySelectorAll('.stab-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.stab-btn').forEach(function(b){ b.classList.remove('active'); });
    var p = document.getElementById('tab-' + name);
    if (p) p.classList.add('active');
    if (btn) btn.classList.add('active');
}

/* Show/hide password */
function togglePw(inputId, iconId) {
    var inp = document.getElementById(inputId);
    var ico = document.getElementById(iconId);
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    if (ico) ico.classList.toggle('fa-eye'); ico && ico.classList.toggle('fa-eye-slash');
}

/* Password strength */
function pwStrength(v) {
    var bar  = document.getElementById('pwBar');
    var hint = document.getElementById('pwHint');
    if (!bar) return;
    if (!v) { bar.className='pw-bar'; hint.textContent=''; return; }
    var sc = 0;
    if (v.length >= 8)          sc++;
    if (/[A-Z]/.test(v))        sc++;
    if (/[0-9]/.test(v))        sc++;
    if (/[^A-Za-z0-9]/.test(v)) sc++;
    if (sc <= 1) { bar.className='pw-bar weak';   hint.className='pw-hint weak';   hint.textContent='Weak'; }
    else if (sc <= 3) { bar.className='pw-bar medium'; hint.className='pw-hint medium'; hint.textContent='Medium strength'; }
    else { bar.className='pw-bar strong';  hint.className='pw-hint strong';  hint.textContent='Strong password ✓'; }
}

/* Flash dismiss */
document.addEventListener('DOMContentLoaded', function() {
    var f = document.getElementById('flashMsg');
    if (f) setTimeout(function(){ f.style.transition='opacity .5s'; f.style.opacity='0'; setTimeout(function(){f.remove();},500); }, 3500);
});
</script>
</body>
</html>
