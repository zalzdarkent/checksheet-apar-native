/**
 * SystemQRScanner.js
 * Utility for live QR Scanning using ZXing
 */

const SystemQRScanner = {
    codeReader: null,
    selectedDeviceId: null,
    stream: null,

    init: function() {
        if (!this.codeReader) {
            this.codeReader = new ZXing.BrowserQRCodeReader();
        }
    },

    startScan: async function(videoElementId, callback) {
        this.init();
        
        // Cek dukungan browser
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            let msg = "Browser Anda tidak mendukung akses kamera.";
            if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                msg = "Kamera diblokir karena koneksi tidak aman (HTTP). Silakan gunakan HTTPS atau Localhost.";
            }
            alert(msg);
            return;
        }

        try {
            // Kita coba dengan facingMode environment (kamera belakang) dulu
            const constraints = {
                video: { 
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            console.log("Mencoba membuka kamera environment...");
            
            try {
                await this.codeReader.decodeFromConstraints(constraints, videoElementId, (result, err) => {
                    if (result) {
                        console.log("QR Result:", result.text);
                        callback(result.text);
                    }
                });
            } catch (innerErr) {
                // Jika gagal (mungkin karena ini laptop/ngga ada kamera belakang), coba kamera default apa saja
                console.warn("Gagal membuka kamera environment, mencoba kamera default...", innerErr);
                await this.codeReader.decodeFromConstraints({ video: true }, videoElementId, (result, err) => {
                    if (result) {
                        callback(result.text);
                    }
                });
            }

        } catch (err) {
            console.error("Scanner Error:", err);
            alert("Gagal mengakses kamera: " + (err.message || err));
        }
    },

    stopScan: function() {
        if (this.codeReader) {
            this.codeReader.reset();
        }
    }
};

window.SystemQRScanner = SystemQRScanner;
