@extends('layout.master')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Admin Baru - Upload KTP (OCR)</h4>
                    <p class="card-description">
                        Upload foto KTP, sistem akan otomatis membaca NIK.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('admin.ktp.store') }}" method="POST"
                        enctype="multipart/form-data" id="ktpForm">
                        @csrf

                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap (Sesuai KTP)</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="form-group">
                            <label>Foto KTP</label>
                            <input type="file" name="ktp_image" class="file-upload-default" id="ktpInput" accept="image/*"
                                required>
                            <div class="input-group col-xs-12">
                                <input type="text" class="form-control file-upload-info" disabled
                                    placeholder="Upload Image">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
                                </span>
                            </div>
                            <small class="text-muted">Pastikan foto jelas dan pencahayaan cukup agar NIK terbaca.</small>
                        </div>

                        <!-- Hidden NIK Input (Auto-filled by OCR) -->
                        <div class="form-group" id="nikGroup" style="display: none;">
                            <label for="nik">NIK (Hasil OCR)</label>
                            <input type="text" class="form-control" id="nik" name="nik" readonly required>
                            <small class="text-success" id="ocrStatus"></small>
                        </div>

                        <div id="loadingOcr" style="display: none;" class="mb-3">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                <span>Sedang memproses foto KTP...</span>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" id="ocrProgress"></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2" id="submitBtn" disabled>Simpan</button>
                        <button type="button" class="btn btn-light" onclick="window.history.back()">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Load Tesseract.js --}}
    <script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ktpInput = document.getElementById('ktpInput');
            const nikInput = document.getElementById('nik');
            const nikGroup = document.getElementById('nikGroup');
            const loadingOcr = document.getElementById('loadingOcr');
            const ocrStatus = document.getElementById('ocrStatus');
            const ocrProgress = document.getElementById('ocrProgress');
            const submitBtn = document.getElementById('submitBtn');

            // Initial file upload styling logic (mimics template)
            document.querySelector('.file-upload-browse').addEventListener('click', function () {
                ktpInput.click();
            });

            ktpInput.addEventListener('change', function () {
                const file = this.files[0];
                const fileName = file ? file.name : "";
                document.querySelector('.file-upload-info').value = fileName;

                if (file) {
                    processImage(file);
                }
            });

            async function processImage(file) {
                // Reset UI
                nikInput.value = '';
                nikGroup.style.display = 'none';
                loadingOcr.style.display = 'block';
                submitBtn.disabled = true;
                ocrProgress.style.width = '0%';

                try {
                    const worker = await Tesseract.createWorker('ind', 1, {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                ocrProgress.style.width = (m.progress * 100) + '%';
                            }
                        }
                    });

                    const ret = await worker.recognize(file);
                    console.log(ret.data.text);

                    // Extract NIK using Regex
                    const text = ret.data.text;
                    // Regex looks for 16 digits, possibly with spaces or common OCR errors
                    // NIK usually starts with 1-9
                    const nikMatch = text.match(/\b[1-9][0-9\s]{15,}\b/) || text.match(/[0-9]{16}/);

                    let detectedNik = '';
                    if (nikMatch) {
                        detectedNik = nikMatch[0].replace(/\s/g, ''); // Remove spaces
                        // Trim to 16 digits if longer
                        detectedNik = detectedNik.substring(0, 16);
                    }

                    await worker.terminate();

                    if (detectedNik && detectedNik.length === 16) {
                        nikInput.value = detectedNik;
                        ocrStatus.innerText = "NIK berhasil ditemukan!";
                        ocrStatus.className = "text-success";
                        submitBtn.disabled = false;
                    } else {
                        nikInput.value = ''; // User must fill manually
                        nikInput.readOnly = false; // Allow manual edit
                        ocrStatus.innerText = "NIK tidak terbaca otomatis. Silakan ketik manual.";
                        ocrStatus.className = "text-warning";
                        submitBtn.disabled = false; // Allow submit even if manual
                    }

                    nikGroup.style.display = 'block';

                } catch (error) {
                    console.error(error);
                    ocrStatus.innerText = "Gagal memproses gambar. Silakan coba lagi atau ketik manual.";
                    ocrStatus.className = "text-danger";
                    nikInput.readOnly = false;
                    nikGroup.style.display = 'block';
                    submitBtn.disabled = false;
                } finally {
                    loadingOcr.style.display = 'none';
                }
            }
        });
    </script>
@endsection