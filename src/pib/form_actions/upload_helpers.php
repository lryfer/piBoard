<?php
namespace pib\form_actions;

class UploadHelpers
{
    public static function renderFileUploadForm(string $action, array $options = []): string
    {
        $boardId = $options['board_id'] ?? '';
        $csrfToken = $options['csrf_token'] ?? '';
        $showTitle = $options['show_title'] ?? false;
        $formId = $options['form_id'] ?? 'upload_form';

        ob_start();
        ?>
        <form id="<?php echo htmlspecialchars($formId); ?>"
              action="<?php echo htmlspecialchars($action); ?>"
              method="post"
              enctype="multipart/form-data">
            <?php echo $csrfToken; ?>

            <?php if ($showTitle): ?>
            <div>
                <input name="title" type="text" placeholder="Title" required>
            </div>
            <?php endif; ?>

            <div>
                <textarea name="content" rows="6" placeholder="Content..." required></textarea>
            </div>

            <div class="media-option-tabs">
                <div class="media-tab active" onclick="switchMediaTab_<?php echo $formId; ?>('upload')">Upload File</div>
                <div class="media-tab" onclick="switchMediaTab_<?php echo $formId; ?>('url')">URL</div>
            </div>

            <div id="media-upload-<?php echo $formId; ?>" class="media-option-content active">
                <div class="file-drop-zone" id="drop_zone_<?php echo $formId; ?>">
                    <div class="icon">📁</div>
                    <p>Drag & drop or click to select</p>
                    <p style="font-size: 0.9rem;">Images (10MB) or Videos (100MB)</p>
                </div>
                <input type="file"
                       name="media_file"
                       id="file_input_<?php echo $formId; ?>"
                       class="file-input-hidden"
                       accept="image/*,video/*">
                <div id="preview_<?php echo $formId; ?>" class="preview-area"></div>
            </div>

            <div id="media-url-<?php echo $formId; ?>" class="media-option-content">
                <input type="url" name="image_url" placeholder="Media URL (optional)">
            </div>

            <?php if (!empty($boardId)): ?>
            <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($boardId); ?>">
            <?php endif; ?>

            <div>
                <input type="submit" value="Submit">
            </div>
        </form>

        <script>
        (function() {
            function switchMediaTab_<?php echo $formId; ?>(tab) {
                const form = document.getElementById('<?php echo $formId; ?>');
                form.querySelectorAll('.media-tab').forEach(t => t.classList.remove('active'));
                form.querySelectorAll('.media-option-content').forEach(c => c.classList.remove('active'));

                if (tab === 'upload') {
                    form.querySelector('.media-tab:first-child').classList.add('active');
                    document.getElementById('media-upload-<?php echo $formId; ?>').classList.add('active');
                } else {
                    form.querySelector('.media-tab:last-child').classList.add('active');
                    document.getElementById('media-url-<?php echo $formId; ?>').classList.add('active');
                }
            }

            window.switchMediaTab_<?php echo $formId; ?> = switchMediaTab_<?php echo $formId; ?>;

            const dropZone = document.getElementById('drop_zone_<?php echo $formId; ?>');
            const fileInput = document.getElementById('file_input_<?php echo $formId; ?>');
            const preview = document.getElementById('preview_<?php echo $formId; ?>');

            if (!dropZone || !fileInput) return;

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
            });

            dropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    showPreview(files[0]);
                }
            });

            dropZone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    showPreview(e.target.files[0]);
                }
            });

            function showPreview(file) {
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                const validVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];

                if (!validImageTypes.includes(file.type) && !validVideoTypes.includes(file.type)) {
                    alert('Invalid file type');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    if (file.type.startsWith('image/')) {
                        preview.innerHTML = '<div class="preview-container"><img src="' + e.target.result + '"><button type="button" class="remove-file" onclick="this.parentElement.parentElement.innerHTML=\'\'; document.getElementById(\'file_input_<?php echo $formId; ?>\').value=\'\'">✕</button></div>';
                    } else if (file.type.startsWith('video/')) {
                        preview.innerHTML = '<div class="preview-container"><video controls src="' + e.target.result + '"></video><button type="button" class="remove-file" onclick="this.parentElement.parentElement.innerHTML=\'\'; document.getElementById(\'file_input_<?php echo $formId; ?>\').value=\'\'">✕</button></div>';
                    }
                };
                reader.readAsDataURL(file);
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}
