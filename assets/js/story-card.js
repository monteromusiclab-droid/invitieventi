/**
 * Story Card JavaScript - v2.5.1
 * Gestisce download PNG ad alta risoluzione e condivisione
 *
 * FIX v2.5.1:
 * - Dimensioni corrette 1080x1920px (Instagram Stories)
 * - Delay per caricamento risorse (fonts, immagini)
 * - Timeout aumentato per html2canvas
 * - Migliore gestione CORS e immagini esterne
 */

(function($) {
    'use strict';

    // Configurazione dimensioni Story Card ottimali per Instagram
    const STORY_CARD_CONFIG = {
        targetWidth: 1080,  // Larghezza Instagram Stories
        targetHeight: 1920, // Altezza Instagram Stories (9:16)
        scale: 2,           // Scala per alta risoluzione (2160x3840px effettivi)
        renderDelay: 1500,  // Delay per caricamento risorse (ms)
        timeout: 30000      // Timeout html2canvas (30 secondi)
    };

    /**
     * Inizializza Story Card functionality
     */
    function initStoryCard() {
        // Download Story come PNG
        $(document).on('click', '.wi-download-story', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const inviteId = $btn.data('invite-id');
            const $storyCard = $('#wi-story-card-' + inviteId);

            if ($storyCard.length === 0) {
                alert('Story Card non trovata!');
                return;
            }

            // Disabilita bottone durante il download
            $btn.prop('disabled', true);
            const originalText = $btn.html();
            $btn.html('<span class="wi-btn-icon">⏳</span> Generazione in corso...');

            // Aggiungi classe loading alla story card
            $storyCard.addClass('loading');

            // Verifica se html2canvas è caricato
            if (typeof html2canvas === 'undefined') {
                // Carica html2canvas dinamicamente se non presente
                loadHtml2Canvas().then(() => {
                    startCaptureProcess($storyCard, inviteId, $btn, originalText);
                }).catch((error) => {
                    console.error('Errore caricamento html2canvas:', error);
                    alert('Errore nel caricamento della libreria. Riprova.');
                    resetButton($btn, originalText, $storyCard);
                });
            } else {
                startCaptureProcess($storyCard, inviteId, $btn, originalText);
            }
        });

        // Condividi Story
        $(document).on('click', '.wi-share-story', function(e) {
            e.preventDefault();

            const inviteUrl = window.location.href;

            if (navigator.share) {
                // Usa Web Share API se disponibile
                navigator.share({
                    title: document.title,
                    url: inviteUrl
                }).catch((error) => {
                    if (error.name !== 'AbortError') {
                        console.error('Errore condivisione:', error);
                        fallbackShare(inviteUrl);
                    }
                });
            } else {
                // Fallback: copia link negli appunti
                fallbackShare(inviteUrl);
            }
        });
    }

    /**
     * Carica html2canvas dinamicamente
     */
    function loadHtml2Canvas() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Avvia il processo di cattura con preparazione e delay
     */
    function startCaptureProcess($storyCard, inviteId, $btn, originalText) {
        console.log('WI Story Card: Inizio processo di cattura per invite ID:', inviteId);

        // Step 1: Attendi caricamento fonts
        $btn.html('<span class="wi-btn-icon">⏳</span> Caricamento fonts...');

        waitForFontsLoad().then(() => {
            console.log('WI Story Card: Fonts caricati');

            // Step 2: Attendi caricamento immagini
            $btn.html('<span class="wi-btn-icon">⏳</span> Caricamento immagini...');

            return waitForImagesLoad($storyCard);
        }).then(() => {
            console.log('WI Story Card: Immagini caricate');

            // Step 3: Delay aggiuntivo per rendering completo
            $btn.html('<span class="wi-btn-icon">⏳</span> Preparazione rendering...');

            return new Promise(resolve => setTimeout(resolve, STORY_CARD_CONFIG.renderDelay));
        }).then(() => {
            console.log('WI Story Card: Rendering delay completato, inizio cattura');

            // Step 4: Prepara Story Card per cattura ad alta risoluzione
            $btn.html('<span class="wi-btn-icon">⏳</span> Generazione PNG...');

            prepareStoryCardForCapture($storyCard);

            // Step 5: Cattura con html2canvas
            return captureStoryCard($storyCard, inviteId);
        }).then((blob) => {
            console.log('WI Story Card: Cattura completata, dimensione blob:', blob.size, 'bytes');

            // Step 6: Download immagine
            const fileName = 'story-invite-' + inviteId + '-' + Date.now() + '.png';
            downloadBlob(blob, fileName);

            // Mostra messaggio successo
            showSuccessMessage($btn);

            // Ripristina Story Card e bottone
            restoreStoryCardAfterCapture($storyCard);
            resetButton($btn, originalText, $storyCard);
        }).catch((error) => {
            console.error('WI Story Card: Errore durante la generazione:', error);
            alert('Errore durante la generazione dell\'immagine. Riprova.\n\nDettagli: ' + error.message);
            restoreStoryCardAfterCapture($storyCard);
            resetButton($btn, originalText, $storyCard);
        });
    }

    /**
     * Attende il caricamento completo dei Google Fonts
     */
    function waitForFontsLoad() {
        if (document.fonts && document.fonts.ready) {
            return document.fonts.ready;
        }
        // Fallback: attendi 1 secondo
        return new Promise(resolve => setTimeout(resolve, 1000));
    }

    /**
     * Attende il caricamento di tutte le immagini nello Story Card
     */
    function waitForImagesLoad($storyCard) {
        return new Promise((resolve) => {
            const images = $storyCard.find('img');

            if (images.length === 0) {
                // Nessuna immagine <img>, controlla background-image
                resolve();
                return;
            }

            let loadedCount = 0;
            const totalImages = images.length;

            images.each(function() {
                const img = this;

                if (img.complete) {
                    loadedCount++;
                } else {
                    $(img).on('load error', function() {
                        loadedCount++;
                        if (loadedCount === totalImages) {
                            resolve();
                        }
                    });
                }
            });

            // Se tutte le immagini sono già caricate
            if (loadedCount === totalImages) {
                resolve();
            }

            // Timeout di sicurezza: 5 secondi
            setTimeout(() => {
                console.warn('WI Story Card: Timeout caricamento immagini, procedo comunque');
                resolve();
            }, 5000);
        });
    }

    /**
     * Prepara Story Card per cattura ad alta risoluzione
     * Temporaneamente aumenta le dimensioni a 1080x1920px
     */
    function prepareStoryCardForCapture($storyCard) {
        const $wrapper = $storyCard.closest('.wi-story-card-wrapper');

        // Salva dimensioni originali
        $storyCard.data('original-max-width', $wrapper.css('max-width'));
        $storyCard.data('original-width', $storyCard.css('width'));

        // Imposta dimensioni target per Instagram Stories
        $wrapper.css({
            'max-width': STORY_CARD_CONFIG.targetWidth + 'px',
            'width': STORY_CARD_CONFIG.targetWidth + 'px'
        });

        $storyCard.css({
            'width': STORY_CARD_CONFIG.targetWidth + 'px',
            'height': STORY_CARD_CONFIG.targetHeight + 'px'
        });

        console.log('WI Story Card: Dimensioni impostate a', STORY_CARD_CONFIG.targetWidth + 'x' + STORY_CARD_CONFIG.targetHeight);
    }

    /**
     * Ripristina dimensioni originali Story Card dopo cattura
     */
    function restoreStoryCardAfterCapture($storyCard) {
        const $wrapper = $storyCard.closest('.wi-story-card-wrapper');

        // Ripristina dimensioni originali
        $wrapper.css({
            'max-width': $storyCard.data('original-max-width') || '500px',
            'width': ''
        });

        $storyCard.css({
            'width': $storyCard.data('original-width') || '',
            'height': ''
        });

        console.log('WI Story Card: Dimensioni ripristinate');
    }

    /**
     * Cattura Story Card con html2canvas e genera blob PNG
     */
    function captureStoryCard($storyCard) {
        return new Promise((resolve, reject) => {
            html2canvas($storyCard[0], {
                backgroundColor: '#000000',
                scale: STORY_CARD_CONFIG.scale,
                logging: false,
                useCORS: true,
                allowTaint: true, // Permetti immagini tainted per evitare CORS block
                foreignObjectRendering: false,
                imageTimeout: STORY_CARD_CONFIG.timeout,
                width: STORY_CARD_CONFIG.targetWidth,
                height: STORY_CARD_CONFIG.targetHeight,
                windowWidth: STORY_CARD_CONFIG.targetWidth,
                windowHeight: STORY_CARD_CONFIG.targetHeight,
                onclone: function(clonedDoc) {
                    // Nel documento clonato, assicurati che lo style sia applicato
                    const clonedCard = clonedDoc.querySelector('.wi-story-card');
                    if (clonedCard) {
                        clonedCard.style.width = STORY_CARD_CONFIG.targetWidth + 'px';
                        clonedCard.style.height = STORY_CARD_CONFIG.targetHeight + 'px';
                    }
                }
            }).then((canvas) => {
                console.log('WI Story Card: Canvas generato -',
                    'Dimensioni effettive:', canvas.width + 'x' + canvas.height,
                    '(target:', (STORY_CARD_CONFIG.targetWidth * STORY_CARD_CONFIG.scale) + 'x' +
                    (STORY_CARD_CONFIG.targetHeight * STORY_CARD_CONFIG.scale) + ')');

                // Converti canvas in blob PNG
                canvas.toBlob((blob) => {
                    if (blob) {
                        resolve(blob);
                    } else {
                        reject(new Error('Impossibile generare blob PNG dal canvas'));
                    }
                }, 'image/png', 1.0); // Qualità massima
            }).catch((error) => {
                reject(error);
            });
        });
    }

    /**
     * Scarica blob come file
     */
    function downloadBlob(blob, fileName) {
        if (window.navigator.msSaveBlob) {
            // IE/Edge legacy
            window.navigator.msSaveBlob(blob, fileName);
        } else {
            // Tutti gli altri browser
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = fileName;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();

            // Cleanup
            setTimeout(() => {
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 100);
        }

        console.log('WI Story Card: Download avviato -', fileName);
    }

    /**
     * Ripristina stato bottone
     */
    function resetButton($btn, originalText, $storyCard) {
        setTimeout(() => {
            $btn.prop('disabled', false);
            $btn.html(originalText);
            $storyCard.removeClass('loading');
        }, 500);
    }

    /**
     * Mostra messaggio successo temporaneo
     */
    function showSuccessMessage($btn) {
        const originalHtml = $btn.html();
        $btn.html('<span class="wi-btn-icon">✅</span> Download Completato!');
        $btn.css('background', '#10b981');

        setTimeout(() => {
            $btn.html(originalHtml);
            $btn.css('background', '');
        }, 3000);
    }

    /**
     * Fallback condivisione (copia link)
     */
    function fallbackShare(url) {
        // Crea input temporaneo per copiare il link
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(url).select();

        try {
            document.execCommand('copy');
            alert('✅ Link copiato negli appunti!\n\nPuoi ora condividerlo dove preferisci:\n' + url);
        } catch (err) {
            alert('⚠️ Impossibile copiare automaticamente.\n\nURL da condividere:\n' + url);
        }

        $temp.remove();
    }

    // Inizializza quando il DOM è pronto
    $(document).ready(function() {
        initStoryCard();
        console.log('WI Story Card: Inizializzato con config:', STORY_CARD_CONFIG);
    });

})(jQuery);
