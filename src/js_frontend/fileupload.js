class FileUploadHandler {
    constructor(dropZoneId, fileInputId, previewId) {
        this.dropZone = document.getElementById(dropZoneId);
        this.fileInput = document.getElementById(fileInputId);
        this.preview = document.getElementById(previewId);
        this.currentFile = null;

        if (!this.dropZone || !this.fileInput) return;

        this.init();
    }

    init() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => this.highlight(), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => this.unhighlight(), false);
        });

        this.dropZone.addEventListener('drop', (e) => this.handleDrop(e), false);
        this.fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files), false);
        this.dropZone.addEventListener('click', () => this.fileInput.click());
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    highlight() {
        this.dropZone.classList.add('drag-over');
    }

    unhighlight() {
        this.dropZone.classList.remove('drag-over');
    }

    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.handleFiles(files);
    }

    handleFiles(files) {
        if (files.length === 0) return;

        const file = files[0];

        if (!this.validateFile(file)) {
            alert('Invalid file type or size. Max 10MB for images, 100MB for videos.');
            return;
        }

        this.currentFile = file;
        this.fileInput.files = files;
        this.showPreview(file);
    }

    validateFile(file) {
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const validVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        const maxImageSize = 10 * 1024 * 1024;
        const maxVideoSize = 100 * 1024 * 1024;

        const isImage = validImageTypes.includes(file.type);
        const isVideo = validVideoTypes.includes(file.type);

        if (!isImage && !isVideo) return false;

        if (isImage && file.size > maxImageSize) return false;
        if (isVideo && file.size > maxVideoSize) return false;

        return true;
    }

    showPreview(file) {
        if (!this.preview) return;

        const reader = new FileReader();

        reader.onload = (e) => {
            if (file.type.startsWith('image/')) {
                this.preview.innerHTML = `
                    <div class="preview-container">
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-file" onclick="fileUploader.removeFile()">✕</button>
                    </div>`;
            } else if (file.type.startsWith('video/')) {
                this.preview.innerHTML = `
                    <div class="preview-container">
                        <video controls src="${e.target.result}"></video>
                        <button type="button" class="remove-file" onclick="fileUploader.removeFile()">✕</button>
                    </div>`;
            }
        };

        reader.readAsDataURL(file);
    }

    removeFile() {
        this.currentFile = null;
        this.fileInput.value = '';
        if (this.preview) {
            this.preview.innerHTML = '';
        }
    }
}
