// Barcode Scanner - Optimized for Speed
// HP: Uses "camera 0, facing back" (main camera)
// Laptop: Uses available camera

let scannerTargetInput = null;
let isVerificationMode = false;
let html5QrCode = null;
let isScanning = false;
let peminjamaScanMode = false; // For scan-and-redirect to peminjaman form

// Detect if device is mobile
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Open Scanner Modal
function openScanner(targetInputId, verifyMode = false) {
    scannerTargetInput = targetInputId;
    isVerificationMode = verifyMode;

    // specific handler for peminjaman search
    if (targetInputId === 'searchPeminjaman') {
        peminjamaScanMode = true;
    }

    const modal = document.getElementById('scanner-modal');
    if (!modal) {
        createScannerModal();
    }
    document.getElementById('scanner-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(startScanner, 100);
}

// Create Scanner Modal
function createScannerModal() {
    if (document.getElementById('scanner-modal')) return;

    const modal = document.createElement('div');
    modal.id = 'scanner-modal';
    modal.className = 'hidden fixed inset-0 z-50 flex items-center justify-center bg-black/90';
    modal.innerHTML = `
        <div class="bg-gray-900 rounded-xl max-w-2xl w-full mx-4 overflow-hidden shadow-2xl">
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-white">Scan Barcode</h3>
                    <p id="camera-label" class="text-xs text-gray-400">Memulai...</p>
                </div>
                <button onclick="closeScanner()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="relative bg-black">
                <div id="scanner-container" class="w-full" style="min-height: 300px;"></div>
                <!-- Custom Scan Guide Overlay - Always visible -->
                <div id="scan-guide" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="relative" style="width: 85%; max-width: 350px; height: 120px;">
                        <!-- Corner brackets -->
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-green-400 rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-green-400 rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-green-400 rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-green-400 rounded-br-lg"></div>
                        <!-- Scan line animation -->
                        <div class="absolute left-2 right-2 h-0.5 bg-red-500 animate-pulse" style="top: 50%; box-shadow: 0 0 8px rgba(239, 68, 68, 0.8);"></div>
                        <!-- Text hint -->
                        <div class="absolute -bottom-8 left-0 right-0 text-center">
                            <span class="text-xs text-gray-300 bg-black/50 px-2 py-1 rounded">Posisikan barcode di dalam kotak</span>
                        </div>
                    </div>
                </div>
                <!-- Zoom & Torch Controls -->
                <div class="absolute bottom-2 left-2 right-2 flex items-center gap-3 bg-black/60 rounded-lg p-2">
                    <!-- Torch/Flash Button -->
                    <button id="torch-btn" onclick="toggleTorch()" class="p-2 text-gray-400 hover:text-yellow-400 rounded-lg transition-colors" title="Flash">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </button>
                    <!-- Zoom Slider -->
                    <div class="flex-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                        </svg>
                        <input type="range" id="zoom-slider" min="1" max="5" step="0.1" value="1" 
                            class="flex-1 h-1 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-green-500"
                            oninput="applyZoom(this.value)">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                        </svg>
                    </div>
                    <span id="zoom-label" class="text-xs text-gray-400 w-10 text-right">1.0x</span>
                </div>
            </div>
            <div class="p-4 bg-gray-800">
                <p id="scanner-status" class="text-sm text-yellow-400 text-center">Memulai kamera...</p>
                <p class="text-xs text-gray-500 text-center mt-1">Barcode kecil? Geser slider zoom ke kanan</p>
                <div id="manual-input-section" class="mt-3 hidden">
                    <div class="flex gap-2">
                        <input type="text" id="manual-barcode-input" 
                            class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                            placeholder="Ketik barcode manual...">
                        <button onclick="submitManualBarcode()" 
                            class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors text-sm font-medium">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Store video track for zoom/torch control
let videoTrack = null;
let torchEnabled = false;

// Apply zoom to camera
function applyZoom(value) {
    const zoomLabel = document.getElementById('zoom-label');
    if (zoomLabel) zoomLabel.textContent = parseFloat(value).toFixed(1) + 'x';

    if (!videoTrack) return;

    try {
        const capabilities = videoTrack.getCapabilities();
        if (capabilities.zoom) {
            const minZoom = capabilities.zoom.min || 1;
            const maxZoom = capabilities.zoom.max || 5;
            const zoomValue = Math.min(Math.max(parseFloat(value), minZoom), maxZoom);
            videoTrack.applyConstraints({ advanced: [{ zoom: zoomValue }] });
        }
    } catch (e) {
        console.log('Zoom not supported on this device');
    }
}

// Toggle torch/flash
function toggleTorch() {
    if (!videoTrack) return;

    try {
        const capabilities = videoTrack.getCapabilities();
        if (capabilities.torch) {
            torchEnabled = !torchEnabled;
            videoTrack.applyConstraints({ advanced: [{ torch: torchEnabled }] });

            const btn = document.getElementById('torch-btn');
            if (btn) {
                btn.classList.toggle('text-yellow-400', torchEnabled);
                btn.classList.toggle('text-gray-400', !torchEnabled);
            }
        }
    } catch (e) {
        console.log('Torch not supported on this device');
    }
}

// Capture video track from scanner for zoom/torch
function captureVideoTrack() {
    try {
        // Get video element from scanner container
        const video = document.querySelector('#scanner-container video');
        if (video && video.srcObject) {
            const tracks = video.srcObject.getVideoTracks();
            if (tracks.length > 0) {
                videoTrack = tracks[0];
                setupZoomSlider();
            }
        }
    } catch (e) {
        console.log('Could not capture video track');
    }
}

// Setup zoom slider based on camera capabilities
function setupZoomSlider() {
    if (!videoTrack) return;

    try {
        const capabilities = videoTrack.getCapabilities();
        const slider = document.getElementById('zoom-slider');

        if (capabilities.zoom && slider) {
            slider.min = capabilities.zoom.min || 1;
            slider.max = Math.min(capabilities.zoom.max || 5, 10); // Cap at 10x
            slider.value = 1;
        }
    } catch (e) {
        console.log('Could not get zoom capabilities');
    }
}

// Start Scanner
async function startScanner() {
    const container = document.getElementById('scanner-container');
    const statusElem = document.getElementById('scanner-status');
    const cameraLabel = document.getElementById('camera-label');

    if (isScanning) return;

    // Check if HTML5QrcodeScanner is available
    if (typeof Html5Qrcode === 'undefined') {
        if (typeof Quagga !== 'undefined') {
            startQuaggaScanner();
            return;
        }
        statusElem.innerHTML = '<b class="text-red-400">Scanner library tidak ditemukan!</b>';
        showManualInput();
        return;
    }

    const config = {
        fps: 30,
        // No qrbox - we use custom overlay and scan full frame for better detection
        aspectRatio: 16 / 9,
        disableFlip: false,
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        }
    };

    try {
        container.innerHTML = '';
        html5QrCode = new Html5Qrcode("scanner-container");

        // For MOBILE: Try to find and use "camera 0, facing back"
        if (isMobileDevice()) {
            statusElem.textContent = "Mencari kamera utama...";
            cameraLabel.textContent = "Mencari...";

            try {
                const cameras = await Html5Qrcode.getCameras();

                if (cameras && cameras.length > 0) {
                    // Find main camera: "camera 0, facing back"
                    const mainCamera = cameras.find(cam => {
                        const label = (cam.label || '').toLowerCase();
                        return (label.includes('camera 0') && label.includes('back')) ||
                            (label.includes('camera0') && label.includes('back')) ||
                            (label.includes('back') && label.includes('0')) ||
                            (label.includes('rear') && !label.includes('wide') && !label.includes('ultra'));
                    });

                    if (mainCamera) {
                        cameraLabel.textContent = "Kamera Utama";
                        statusElem.textContent = "Mengakses kamera utama...";

                        await html5QrCode.start(
                            { deviceId: { exact: mainCamera.id } },
                            config,
                            onScanSuccess,
                            onScanError
                        );

                        isScanning = true;
                        captureVideoTrack(); // Get video track for zoom/torch
                        statusElem.innerHTML = '<span class="text-green-400">✓ Scanner Aktif</span> <span class="text-gray-400 text-xs">- Kamera Utama</span>';
                        return;
                    }
                }
            } catch (e) {
                console.log('Could not get cameras, falling back to facingMode');
            }
        }

        // For LAPTOP or fallback: Use facingMode environment
        cameraLabel.textContent = "Kamera Belakang";
        statusElem.textContent = "Mengakses kamera...";

        await html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanError
        );

        isScanning = true;
        captureVideoTrack(); // Get video track for zoom/torch
        statusElem.innerHTML = '<span class="text-green-400">✓ Scanner Aktif</span>';

    } catch (err) {
        console.error('Scanner error:', err);
        statusElem.innerHTML = `<span class="text-red-400">Gagal: ${err.message || err}</span>`;
        showManualInput();

        // Try Quagga as fallback
        if (typeof Quagga !== 'undefined') {
            statusElem.innerHTML += '<br><span class="text-yellow-400 text-xs">Mencoba scanner alternatif...</span>';
            setTimeout(startQuaggaScanner, 500);
        }
    }
}

// Quagga Fallback Scanner
function startQuaggaScanner() {
    const container = document.getElementById('scanner-container');
    const statusElem = document.getElementById('scanner-status');
    const cameraLabel = document.getElementById('camera-label');

    container.innerHTML = '';
    cameraLabel.textContent = "Mode Alternatif";

    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: container,
            constraints: {
                width: { min: 640, ideal: 1280 },
                height: { min: 480, ideal: 720 },
                facingMode: "environment"
            },
            area: { top: "0%", right: "0%", left: "0%", bottom: "0%" }
        },
        locator: {
            patchSize: "large",
            halfSample: false
        },
        numOfWorkers: navigator.hardwareConcurrency || 4,
        frequency: 20,
        decoder: {
            readers: ["code_128_reader", "code_39_reader", "ean_reader", "ean_8_reader"],
            multiple: false
        },
        locate: true
    }, function (err) {
        if (err) {
            console.error('Quagga error:', err);
            statusElem.innerHTML = '<span class="text-red-400">Gagal akses kamera</span>';
            showManualInput();
            return;
        }
        Quagga.start();
        isScanning = true;
        statusElem.innerHTML = '<span class="text-green-400">✓ Scanner Aktif</span> <span class="text-gray-400 text-xs">(Alternatif)</span>';
    });

    Quagga.onDetected(function (result) {
        if (result && result.codeResult) {
            onScanSuccess(result.codeResult.code);
        }
    });
}

// Handle successful scan
function onScanSuccess(decodedText) {
    const code = typeof decodedText === 'string' ? decodedText : decodedText.code || decodedText;
    if (!code) return;

    // Vibrate feedback
    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);

    // Play success sound
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        oscillator.frequency.value = 1200;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.1;
        oscillator.start();
        setTimeout(() => oscillator.stop(), 100);
    } catch (e) { }

    // Check if in peminjaman mode
    if (typeof peminjamaScanMode !== 'undefined' && peminjamaScanMode) {
        peminjamaScanMode = false;
        closeScanner();

        // Show loading
        showToast('Mencari barang...', 'info');

        // Lookup barang by barcode and redirect
        fetch('/barang/api/barcode/' + encodeURIComponent(code))
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const barang = data.data;

                    // Check if barang is available
                    if (barang.status_ketersediaan !== 'tersedia') {
                        showToast('Barang tidak tersedia untuk dipinjam', 'warning');
                        return;
                    }

                    // Redirect to form
                    showToast('Barang ditemukan: ' + barang.nama_barang, 'success');
                    setTimeout(() => {
                        window.location.href = '/peminjaman/form?barang_id=' + barang.id;
                    }, 500);
                } else {
                    showToast('Barang dengan barcode tersebut tidak ditemukan', 'error');
                }
            })
            .catch(err => {
                console.error('Lookup error:', err);
                showToast('Gagal mencari barang', 'error');
            });

        return;
    }

    // Normal mode - need scannerTargetInput
    if (!scannerTargetInput) return;

    // Handle verification mode
    if (isVerificationMode && window.expectedBarcode) {
        const scanResult = document.getElementById('scan-result');
        if (scanResult) {
            if (code === window.expectedBarcode) {
                scanResult.innerHTML = '✓ Barcode cocok!<br><span class="text-xs font-mono">' + code + '</span>';
                scanResult.className = 'mt-3 px-3 py-2 rounded-lg text-sm bg-green-500/10 text-green-400 border border-green-500/30';
                document.getElementById(scannerTargetInput).value = code;
            } else {
                scanResult.innerHTML = '✗ Barcode tidak cocok!<br><span class="text-xs text-gray-400">Expected: ' + window.expectedBarcode + '<br>Scanned: ' + code + '</span>';
                scanResult.className = 'mt-3 px-3 py-2 rounded-lg text-sm bg-red-500/10 text-red-400 border border-red-500/30';
            }
            scanResult.classList.remove('hidden');
        }
    } else {
        const input = document.getElementById(scannerTargetInput);
        if (input) {
            input.value = code;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    closeScanner();
    showToast('Barcode: ' + code, 'success');
}

// Handle scan errors (silent)
function onScanError(errorMessage) { }

// Show manual input
function showManualInput() {
    const section = document.getElementById('manual-input-section');
    if (section) section.classList.remove('hidden');
}

// Submit manual barcode
function submitManualBarcode() {
    const input = document.getElementById('manual-barcode-input');
    if (input && input.value.trim()) {
        onScanSuccess(input.value.trim());
    }
}

// Close Scanner
function closeScanner() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().catch(() => { });
        html5QrCode = null;
    }

    if (typeof Quagga !== 'undefined') {
        try { Quagga.stop(); } catch (e) { }
    }

    isScanning = false;
    videoTrack = null;
    torchEnabled = false;

    // Reset zoom slider
    const slider = document.getElementById('zoom-slider');
    if (slider) slider.value = 1;
    const zoomLabel = document.getElementById('zoom-label');
    if (zoomLabel) zoomLabel.textContent = '1.0x';

    const modal = document.getElementById('scanner-modal');
    if (modal) modal.classList.add('hidden');
    document.body.style.overflow = '';
    scannerTargetInput = null;
    isVerificationMode = false;
}

// ============================================
// Camera Functions for Photo Capture
// ============================================
let cameraStream = null;

function openCamera() {
    const modal = document.getElementById('camera-modal');
    if (!modal) createCameraModal();
    document.getElementById('camera-modal').classList.remove('hidden');
    startCamera();
}

function createCameraModal() {
    if (document.getElementById('camera-modal')) return;

    const modal = document.createElement('div');
    modal.id = 'camera-modal';
    modal.className = 'hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80';
    modal.innerHTML = `
        <div class="bg-gray-800 rounded-xl p-6 max-w-lg w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Ambil Foto</h3>
                <button onclick="closeCamera()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="relative">
                <video id="camera-video" class="w-full aspect-video bg-black rounded-lg" autoplay playsinline></video>
                <canvas id="camera-canvas" class="hidden"></canvas>
            </div>
            <div class="flex justify-center mt-4">
                <button onclick="capturePhoto()" class="p-4 bg-violet-500 text-white rounded-full hover:bg-violet-600 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

async function startCamera() {
    const video = document.getElementById('camera-video');
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        video.srcObject = cameraStream;
    } catch (err) {
        console.error('Camera error:', err);
        showToast('Gagal mengakses kamera', 'error');
        closeCamera();
    }
}

function capturePhoto() {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    if (!video || !canvas) return;

    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);

    const dataURL = canvas.toDataURL('image/jpeg', 0.8);
    const preview = document.getElementById('photo_preview');
    if (preview) {
        preview.classList.remove('hidden');
        const div = document.createElement('div');
        div.className = 'relative aspect-square bg-gray-900 rounded-lg overflow-hidden';
        div.innerHTML = `
            <img src="${dataURL}" class="w-full h-full object-cover">
            <span class="absolute bottom-1 left-1 px-2 py-0.5 text-xs bg-teal-500 text-white rounded">Captured</span>
        `;
        preview.appendChild(div);
    }

    canvas.toBlob(function (blob) {
        const file = new File([blob], 'photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });
        const fileInput = document.querySelector('input[name="fotos"]');
        if (fileInput) {
            const dataTransfer = new DataTransfer();
            for (let i = 0; i < fileInput.files.length; i++) {
                dataTransfer.items.add(fileInput.files[i]);
            }
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
        }
    }, 'image/jpeg', 0.8);

    showToast('Foto berhasil diambil!', 'success');
    closeCamera();
}

function closeCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    const modal = document.getElementById('camera-modal');
    if (modal) modal.classList.add('hidden');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function () {
    if (cameraStream) cameraStream.getTracks().forEach(track => track.stop());
    if (html5QrCode && isScanning) html5QrCode.stop().catch(() => { });
    if (typeof Quagga !== 'undefined') { try { Quagga.stop(); } catch (e) { } }
});

// Handle escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeScanner();
        closeCamera();
    }
});

// ============================================
// Scan for Peminjaman - Scan and Redirect
// ============================================
function openScannerForPeminjaman() {
    peminjamaScanMode = true;
    scannerTargetInput = null;
    isVerificationMode = false;

    const modal = document.getElementById('scanner-modal');
    if (!modal) {
        createScannerModal();
    }
    document.getElementById('scanner-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(startScanner, 100);
}

