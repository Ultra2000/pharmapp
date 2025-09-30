window.addEventListener('filament:components-loaded', () => {
    console.log('barcode-scanner.js loaded');
    const scanBtn = document.getElementById('scan-barcode-btn');
    if (!scanBtn) return;

    scanBtn.addEventListener('click', async () => {
        console.log('Scan code-barres button clicked');
        const codeReader = new BrowserMultiFormatReader();
        const videoElement = document.createElement('video');
        videoElement.setAttribute('id', 'barcode-video');
        videoElement.setAttribute('style', 'width:100%;max-width:400px;');
        scanBtn.parentNode.appendChild(videoElement);

        try {
            const devices = await codeReader.listVideoInputDevices();
            if (devices.length > 0) {
                codeReader.decodeFromVideoDevice(devices[0].deviceId, 'barcode-video', (result, err) => {
                    if (result) {
                        document.getElementById('barcode-input').value = result.text;
                        codeReader.reset();
                        videoElement.remove();
                    }
                });
            }
        } catch (e) {
            alert('Erreur accès caméra : ' + e.message);
        }
    });
});
