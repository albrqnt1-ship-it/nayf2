<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء بطاقة جديدة - شبكة البرق</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>➕ إنشاء بطاقة جديدة</h1>
                <p>إضافة بطاقة مع رفع الحلقات والملصقات</p>
            </div>
            <div class="header-actions">
                <a href="index.html" class="btn btn-secondary" style="width: auto; padding: 10px 20px;">
                    ← العودة للوحة التحكم
                </a>
            </div>
        </div>
        
        <div class="glass-card">
            <form id="createCardForm">
                <div class="form-group">
                    <label for="card_number">رقم البطاقة (أي طول)</label>
                    <input type="text" id="card_number" name="card_number" placeholder="أدخل رقم البطاقة" required>
                </div>
                
                <div class="form-group">
                    <label for="max_episodes">عدد الحلقات المطلوبة</label>
                    <input type="number" id="max_episodes" name="max_episodes" min="1" placeholder="عدد الحلقات" required>
                </div>
                
                <div class="form-group">
                    <label for="max_devices">عدد الأجهزة المسموح بها</label>
                    <input type="number" id="max_devices" name="max_devices" min="1" value="1" required>
                </div>
                
                <button type="submit" class="btn btn-primary">إنشاء البطاقة</button>
            </form>
        </div>
        
        <div id="upload-section" class="glass-card" style="display: none; margin-top: 20px;">
            <h2>رفع الحلقات والملصقات</h2>
            <p style="margin-bottom: 20px;">البطاقة: <strong id="created-card-number"></strong> | الحد الأقصى: <strong id="max-episodes-display"></strong> حلقة</p>
            
            <div class="upload-area" id="uploadArea">
                <h3>🎬 اسحب وأفلت الملفات هنا</h3>
                <p>أو انقر للاختيار</p>
                <p style="font-size: 0.9rem; margin-top: 10px; color: rgba(255,255,255,0.7);">
                    صيغ الفيديو: MP4, MKV, AVI, MOV, WEBM<br>
                    صيغ الصور: JPG, PNG, GIF, WEBP
                </p>
            </div>
            
            <input type="file" id="videoFiles" multiple accept="video/*" style="display: none;">
            <input type="file" id="posterFiles" multiple accept="image/*" style="display: none;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <button onclick="document.getElementById('videoFiles').click()" class="btn btn-primary">
                    🎥 اختر ملفات الفيديو
                </button>
                <button onclick="document.getElementById('posterFiles').click()" class="btn btn-secondary">
                    🖼️ اختر الملصقات
                </button>
            </div>
            
            <div id="filePreview" class="file-preview"></div>
            
            <button id="uploadBtn" class="btn btn-success" style="display: none;" onclick="uploadFiles()">
                ⬆️ رفع جميع الملفات
            </button>
        </div>
    </div>
    
    <script src="../js/main.js"></script>
    <script>
        let createdCardNumber = '';
        let maxEpisodes = 0;
        let selectedVideos = [];
        let selectedPosters = [];
        
        document.getElementById('createCardForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const cardNumber = document.getElementById('card_number').value;
            const maxEpisodesVal = document.getElementById('max_episodes').value;
            const maxDevices = document.getElementById('max_devices').value;
            
            showLoading(true);
            
            const formData = new FormData();
            formData.append('card_number', cardNumber);
            formData.append('max_episodes', maxEpisodesVal);
            formData.append('max_devices', maxDevices);
            
            const result = await apiRequest('create-card.php', 'POST', formData);
            
            showLoading(false);
            
            if (result.success) {
                showAlert(result.message, 'success');
                createdCardNumber = cardNumber;
                maxEpisodes = parseInt(maxEpisodesVal);
                
                document.getElementById('created-card-number').textContent = cardNumber;
                document.getElementById('max-episodes-display').textContent = maxEpisodes;
                document.getElementById('upload-section').style.display = 'block';
                document.getElementById('createCardForm').style.display = 'none';
            } else {
                showAlert(result.message, 'error');
            }
        });
        
        document.getElementById('videoFiles').addEventListener('change', (e) => {
            handleVideoSelection(e.target.files);
        });
        
        document.getElementById('posterFiles').addEventListener('change', (e) => {
            handlePosterSelection(e.target.files);
        });
        
        function handleVideoSelection(files) {
            if (selectedVideos.length + files.length > maxEpisodes) {
                showAlert(`لا يمكن رفع أكثر من ${maxEpisodes} حلقة`, 'error');
                return;
            }
            
            selectedVideos = [...selectedVideos, ...Array.from(files)];
            updateFilePreview();
        }
        
        function handlePosterSelection(files) {
            selectedPosters = [...selectedPosters, ...Array.from(files)];
            updateFilePreview();
        }
        
        function updateFilePreview() {
            const preview = document.getElementById('filePreview');
            
            if (selectedVideos.length === 0 && selectedPosters.length === 0) {
                preview.innerHTML = '';
                document.getElementById('uploadBtn').style.display = 'none';
                return;
            }
            
            preview.innerHTML = `
                <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                    <h3>الملفات المحددة:</h3>
                    <p>🎥 الفيديوهات: ${selectedVideos.length}</p>
                    <p>🖼️ الملصقات: ${selectedPosters.length}</p>
                    <button onclick="clearSelection()" class="btn btn-danger" style="padding: 8px 15px; font-size: 0.9rem; margin-top: 10px;">
                        ✖️ مسح الاختيار
                    </button>
                </div>
                <div style="max-height: 200px; overflow-y: auto;">
                    ${selectedVideos.map((file, i) => `
                        <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; margin-bottom: 8px;">
                            📹 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                            ${selectedPosters[i] ? `<br>🖼️ ${selectedPosters[i].name}` : ''}
                        </div>
                    `).join('')}
                </div>
            `;
            
            document.getElementById('uploadBtn').style.display = 'block';
        }
        
        function clearSelection() {
            selectedVideos = [];
            selectedPosters = [];
            document.getElementById('videoFiles').value = '';
            document.getElementById('posterFiles').value = '';
            updateFilePreview();
        }
        
        async function uploadFiles() {
            if (selectedVideos.length === 0) {
                showAlert('الرجاء اختيار ملفات الفيديو', 'error');
                return;
            }
            
            if (selectedVideos.length > maxEpisodes) {
                showAlert(`لا يمكن رفع أكثر من ${maxEpisodes} حلقة`, 'error');
                return;
            }
            
            showLoading(true);
            
            const formData = new FormData();
            formData.append('card_number', createdCardNumber);
            
            selectedVideos.forEach((file, index) => {
                formData.append('videos[]', file);
                if (selectedPosters[index]) {
                    formData.append('posters[]', selectedPosters[index]);
                }
            });
            
            const result = await apiRequest('upload-episodes.php', 'POST', formData);
            
            showLoading(false);
            
            if (result.success) {
                showAlert(`تم رفع ${result.data.uploaded_count} حلقة بنجاح!`, 'success');
                
                if (result.data.errors && result.data.errors.length > 0) {
                    result.data.errors.forEach(error => {
                        showAlert(error, 'error');
                    });
                }
                
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 3000);
            } else {
                showAlert(result.message, 'error');
            }
        }
        
        const uploadArea = document.getElementById('uploadArea');
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files);
            const videos = files.filter(f => f.type.startsWith('video/'));
            const images = files.filter(f => f.type.startsWith('image/'));
            
            if (videos.length > 0) handleVideoSelection(videos);
            if (images.length > 0) handlePosterSelection(images);
        });
    </script>
</body>
</html>
