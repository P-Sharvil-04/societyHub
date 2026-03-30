<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>OCR + Signature · Documents</title>
	<style>
		:root {
			--bg: #f4f6f8;
			--card: #fff;
			--primary: #3498db;
			--muted: #6b7280;
		}

		body {
			font-family: Inter, system-ui, Arial, sans-serif;
			background: var(--bg);
			margin: 0;
			padding: 24px
		}

		.container {
			max-width: 1100px;
			margin: 0 auto;
			display: grid;
			grid-template-columns: 320px 1fr;
			gap: 18px
		}

		.card {
			background: var(--card);
			padding: 18px;
			border-radius: 10px;
			box-shadow: 0 6px 20px rgba(0, 0, 0, .04)
		}

		h2 {
			margin: 0 0 12px 0
		}

		.form-row {
			display: flex;
			gap: 8px
		}

		input[type=text],
		select,
		input[type=file],
		button {
			width: 100%;
			padding: 10px;
			border-radius: 6px;
			border: 1px solid #e6e9ef;
			background: #fff
		}

		button {
			background: var(--primary);
			color: #fff;
			border: none;
			cursor: pointer
		}

		.small {
			font-size: 0.85rem;
			color: var(--muted)
		}

		.list-item {
			padding: 10px;
			border-radius: 6px;
			border: 1px solid #eee;
			margin-bottom: 8px;
			display: flex;
			justify-content: space-between;
			align-items: center
		}

		.list-item a {
			color: inherit;
			text-decoration: none;
			display: block;
			width: 100%
		}

		.output {
			background: #fafafa;
			padding: 14px;
			border-radius: 6px;
			white-space: pre-wrap;
			border: 1px solid #f0f0f0
		}

		.signature-preview {
			max-width: 320px;
			border: 1px solid #eee;
			padding: 10px;
			border-radius: 6px;
			background: #fff
		}

		.canvas-wrap {
			border: 1px dashed #ddd;
			padding: 8px;
			border-radius: 6px
		}

		.controls {
			display: flex;
			gap: 8px;
			margin-top: 10px
		}

		.controls button {
			flex: 1
		}

		.flash {
			padding: 10px;
			border-radius: 6px;
			margin-bottom: 12px
		}

		.flash.success {
			background: #ecfdf5;
			color: #065f46
		}

		.flash.error {
			background: #fff1f2;
			color: #9f1239
		}

		@media(max-width:880px) {
			.container {
				grid-template-columns: 1fr;
			}
		}
	</style>
</head>

<body>

	<div class="container">
		<?php $activePage = 'documents'; ?>

		<?php include('sidebar.php') ?>

		<!-- LEFT: upload + documents list -->
		<div class="card">
			<?php if ($this->session->flashdata('success')): ?>
				<div style="max-width:1100px;margin:0 auto 12px;">
					<div class="flash success">
						<?= $this->session->flashdata('success') ?>
					</div>
				</div>
			<?php endif; ?>
			<?php if ($this->session->flashdata('error')): ?>
				<div style="max-width:1100px;margin:0 auto 12px;">
					<div class="flash error">
						<?= $this->session->flashdata('error') ?>
					</div>
				</div>
			<?php endif; ?>
			<h2>Upload / Documents</h2>

			<!-- Upload form -->
			<form method="post" enctype="multipart/form-data" action="<?= site_url('document_controller/read') ?>">
				<label class="small">Title</label>
				<input type="text" name="document_name" placeholder="Document title" required>

				<label class="small">Type</label>
				<select name="document_type">
					<option value="Notice">Notice</option>
					<option value="Bill">Bill</option>
					<option value="Letter">Letter</option>
				</select>

				<label class="small">Image</label>
				<input type="file" name="image" accept="image/*" required>

				<button type="submit" style="margin-top:10px">📤 Upload & Read</button>
			</form>

			<hr style="margin:14px 0;border:none;border-top:1px solid #f0f0f0">

			<h3 style="margin-top:0">All Documents</h3>
			<div class="small" style="margin-bottom:8px">Click a document to view details & sign</div>

			<?php if (!empty($documents)): ?>
				<?php foreach ($documents as $d): ?>
					<div class="list-item">
						<a href="<?= site_url('document_controller?id=' . $d['id']) ?>">
							<div style="font-weight:600">
								<?= htmlspecialchars($d['document_title'] ?? '') ?>
							</div>
							<div class="small"><?= htmlspecialchars($d['document_type'] ?? '') ?> · ID: <?= $d['id'] ?></div>
						</a>
						<div style="margin-left:8px;font-size:0.9rem;color:var(--muted)">
							<?= ($d['signature_path'] ?? '') ? '✔' : '—' ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="small">No documents yet.</div>
			<?php endif; ?>
		</div>

		<!-- RIGHT: document details, extracted text, signature -->
		<div class="card">
			<?php if (empty($document)): ?>
				<h2>No document selected</h2>
				<div class="small">Upload a document or click one from the list on left to view its extracted text and sign
					it.</div>
			<?php else: ?>
				<h2><?= htmlspecialchars($document['document_title'] ?? '') ?></h2>
				<div class="small">
					Type: <?= htmlspecialchars($document['document_type'] ?? '') ?> · ID: <?= $document['id'] ?? '' ?>
					<?php if (!empty($document['created_at'])): ?> · Uploaded:
						<?= htmlspecialchars($document['created_at']) ?>
					<?php endif; ?>
				</div>

				<hr style="margin:12px 0;border:none;border-top:1px solid #f0f0f0">

				<h3 style="margin-bottom:6px">📄 Extracted Text</h3>
				<?php if (!empty($text)): ?>
					<div class="output"><?= nl2br(htmlspecialchars($text)) ?></div>
				<?php else: ?>
					<div class="small">No text extracted (OCR may have failed or produced empty result).</div>
				<?php endif; ?>

				<hr style="margin:12px 0;border:none;border-top:1px solid #f0f0f0">

				<div style="display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap">
					<!-- left column: signing UI -->
					<div style="flex:1;min-width:320px">
						<h3 style="margin-bottom:8px">✍ Sign Document</h3>

						<?php if (!empty($document['signature_path'])): ?>
							<div class="small" style="margin-bottom:8px">This document is already signed (you can re-sign to
								replace).</div>
						<?php else: ?>
							<div class="small" style="margin-bottom:8px">No signature yet — draw below and press OK.</div>
						<?php endif; ?>

						<div class="canvas-wrap">
							<canvas id="canvas" width="700" height="120" style="width:100%;height:120px;"></canvas>
						</div>

						<div class="controls">
							<button type="button" onclick="clearPad()">Clear</button>
							<button type="button" onclick="saveSignature()">OK</button>
						</div>

						<!-- sign form (separate) -->
						<form id="signForm" method="post" action="<?= site_url('document_controller/save_signature') ?>">
							<input type="hidden" name="signature_image" id="signature_image">
							<input type="hidden" name="document_id" id="document_id"
								value="<?= intval($document['id'] ?? 0) ?>">
							<?php if ($this->config->item('csrf_protection')): ?>
								<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
									value="<?= $this->security->get_csrf_hash() ?>">
							<?php endif; ?>
						</form>
					</div>

					<!-- right column: signature preview / info -->
					<div style="width:320px">
						<h3 style="margin-bottom:8px">🖋 Signature Preview</h3>

						<?php if (!empty($document['signature_path']) && file_exists(FCPATH . $document['signature_path'])): ?>
							<div class="signature-preview">
								<img src="<?= base_url($document['signature_path']) ?>" alt="Signature"
									style="max-width:100%;height:auto;display:block">
								<div class="small" style="margin-top:8px">Signed at:
									<?= htmlspecialchars($document['signed_at'] ?? '') ?>
								</div>
								<div class="small">Signed by : <?= htmlspecialchars($document['signed_by'] ?? '') ?>
								</div>
							</div>
						<?php else: ?>
							<div class="small">No signature image available. After you click OK the image will be saved and
								displayed here.</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
		// Canvas drawing: pointer & touch friendly
		const canvas = document.getElementById('canvas');
		if (canvas) {
			const ctx = canvas.getContext('2d');
			let drawing = false;

			function getPos(e) {
				const rect = canvas.getBoundingClientRect();
				if (e.touches && e.touches.length) {
					return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
				}
				if (typeof e.offsetX !== 'undefined') return { x: e.offsetX, y: e.offsetY };
				return { x: e.clientX - rect.left, y: e.clientY - rect.top };
			}

			function startDraw(e) { e.preventDefault(); drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }
			function moveDraw(e) { if (!drawing) return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#000'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke(); }
			function stopDraw(e) { if (!drawing) return; e.preventDefault(); drawing = false; ctx.beginPath(); }

			if (window.PointerEvent) {
				canvas.addEventListener('pointerdown', startDraw);
				canvas.addEventListener('pointermove', moveDraw);
				canvas.addEventListener('pointerup', stopDraw);
				canvas.addEventListener('pointercancel', stopDraw);
			} else {
				canvas.addEventListener('touchstart', startDraw, { passive: false });
				canvas.addEventListener('touchmove', moveDraw, { passive: false });
				canvas.addEventListener('touchend', stopDraw);
				canvas.addEventListener('mousedown', startDraw);
				canvas.addEventListener('mousemove', moveDraw);
				canvas.addEventListener('mouseup', stopDraw);
				canvas.addEventListener('mouseleave', stopDraw);
			}

			window.clearPad = function () { ctx.clearRect(0, 0, canvas.width, canvas.height); }
			function isCanvasBlank(c) {
				const blank = document.createElement('canvas');
				blank.width = c.width; blank.height = c.height;
				return c.toDataURL() === blank.toDataURL();
			}

			window.saveSignature = function () {
				const docId = document.getElementById('document_id').value;
				if (!docId || docId == '0') { alert('Document ID missing. Select or upload a document first.'); return; }
				if (isCanvasBlank(canvas)) { alert('Please draw your signature first.'); return; }
				const dataURL = canvas.toDataURL('image/png');
				document.getElementById('signature_image').value = dataURL;
				document.getElementById('signForm').submit();
			}
		}
	</script>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>

</html>
