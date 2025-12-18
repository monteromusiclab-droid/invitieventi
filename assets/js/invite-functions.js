/**
 * Funzioni JavaScript per gli inviti pubblicati
 * Gestisce: Countdown, Mappa, Condivisione, Calendario
 */

// Funzione per aggiungere l'evento al calendario
function addToCalendar() {
    // Ottieni i dati dall'invito
    var title = document.querySelector('.wi-title') ? document.querySelector('.wi-title').textContent : 'Evento Speciale';
    var message = document.querySelector('.wi-message-content, .wi-message-card, .wi-card') ? 
                  document.querySelector('.wi-message-content, .wi-message-card, .wi-card').textContent.trim() : '';
    var location = document.querySelector('.wi-detail-content p, .wi-detail-text') ? 
                   Array.from(document.querySelectorAll('.wi-detail-content p, .wi-detail-text'))[2].textContent : '';
    
    // Ottieni data e ora dall'elemento countdown
    var countdownEl = document.getElementById('countdown');
    if (!countdownEl) {
        alert('Impossibile ottenere i dati dell\'evento');
        return;
    }
    
    var eventDateStr = countdownEl.getAttribute('data-event-date');
    var eventTimeStr = countdownEl.getAttribute('data-event-time');
    
    if (!eventDateStr || !eventTimeStr) {
        alert('Dati evento non trovati');
        return;
    }
    
    // Converti in formato ISO per Google Calendar
    var startDateTime = eventDateStr + 'T' + eventTimeStr;
    var endDateTime = eventDateStr + 'T' + addHours(eventTimeStr, 3); // +3 ore di durata default
    
    // Crea URL Google Calendar
    var googleCalendarUrl = 'https://www.google.com/calendar/render?action=TEMPLATE' +
        '&text=' + encodeURIComponent(title) +
        '&dates=' + startDateTime.replace(/[-:]/g, '') + '/' + endDateTime.replace(/[-:]/g, '') +
        '&details=' + encodeURIComponent(message) +
        '&location=' + encodeURIComponent(location) +
        '&sf=true&output=xml';
    
    window.open(googleCalendarUrl, '_blank');
}

// Funzione helper per aggiungere ore
function addHours(time, hours) {
    var parts = time.split(':');
    var hour = parseInt(parts[0]) + hours;
    if (hour >= 24) hour = hour - 24;
    return String(hour).padStart(2, '0') + ':' + parts[1] + ':00';
}

// Condivisione WhatsApp
function shareWhatsApp() {
    var url = window.location.href;
    var title = document.querySelector('.wi-title') ? document.querySelector('.wi-title').textContent : 'Vieni al mio evento!';
    var text = encodeURIComponent(title + '\n\n' + url);
    var whatsappUrl = 'https://wa.me/?text=' + text;
    window.open(whatsappUrl, '_blank');
}

// Condivisione Email
function shareEmail() {
    var url = window.location.href;
    var title = document.querySelector('.wi-title') ? document.querySelector('.wi-title').textContent : 'Invito Speciale';
    var message = document.querySelector('.wi-message-content, .wi-message-card, .wi-card') ? 
                  document.querySelector('.wi-message-content, .wi-message-card, .wi-card').textContent.trim() : '';
    
    var subject = encodeURIComponent('Invito: ' + title);
    var body = encodeURIComponent(message + '\n\nClicca qui per vedere tutti i dettagli:\n' + url);
    
    var mailtoUrl = 'mailto:?subject=' + subject + '&body=' + body;
    window.location.href = mailtoUrl;
}

// Copia link
function copyLink() {
    var url = window.location.href;
    
    // Prova con l'API moderna
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            showCopyNotification('Link copiato negli appunti!');
        }).catch(function(err) {
            fallbackCopyLink(url);
        });
    } else {
        fallbackCopyLink(url);
    }
}

// Fallback per browser più vecchi
function fallbackCopyLink(url) {
    var tempInput = document.createElement('input');
    tempInput.value = url;
    tempInput.style.position = 'fixed';
    tempInput.style.opacity = '0';
    document.body.appendChild(tempInput);
    tempInput.select();
    
    try {
        document.execCommand('copy');
        showCopyNotification('Link copiato negli appunti!');
    } catch (err) {
        alert('Link: ' + url + '\n\nCopia manualmente questo link');
    }
    
    document.body.removeChild(tempInput);
}

// Mostra notifica di copia
function showCopyNotification(message) {
    var notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #00a32a; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 999999; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px;';
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(function() {
            document.body.removeChild(notification);
        }, 300);
    }, 2500);
}


/* aggiunto Visualizza Mappa */

document.addEventListener("DOMContentLoaded", function () {
    const mapDiv = document.querySelector('.wi-map[data-address]');
    if (mapDiv) {
        const address = mapDiv.getAttribute('data-address');
        if (address) {
            mapDiv.innerHTML = `
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    style="border:0"
                    src="https://www.google.com/maps?q=${encodeURIComponent(address)}&output=embed"
                    allowfullscreen>
                </iframe>
            `;
        }
    }
});

// Inizializza mappa Google Maps - DISABILITATO (Ora usiamo OpenStreetMap in class-wi-templates.php)
/* FUNZIONE DISABILITATA
function initMap() {
    var mapElement = document.getElementById('map');
    
    if (!mapElement) {
        console.log('Elemento mappa non trovato');
        return;
    }
    
    var address = mapElement.getAttribute('data-address');
    
    if (!address) {
        console.log('Indirizzo non specificato');
        return;
    }
    
    // Verifica se Google Maps è caricato
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.log('Google Maps non caricato');
        mapElement.innerHTML = '<div style="padding: 40px; text-align: center; background: #f0f0f0; border-radius: 10px;"><p>Mappa non disponibile. <a href="https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address) + '" target="_blank" style="color: #2271b1; font-weight: 600;">Apri in Google Maps</a></p></div>';
        return;
    }
    
    // Geocodifica indirizzo
    var geocoder = new google.maps.Geocoder();
    
    geocoder.geocode({ 'address': address }, function(results, status) {
        if (status === 'OK') {
            var location = results[0].geometry.location;
            
            var map = new google.maps.Map(mapElement, {
                zoom: 15,
                center: location,
                styles: [
                    {
                        "featureType": "poi",
                        "elementType": "labels",
                        "stylers": [{ "visibility": "off" }]
                    }
                ]
            });
            
            var marker = new google.maps.Marker({
                position: location,
                map: map,
                title: address,
                animation: google.maps.Animation.DROP
            });
            
            var infoWindow = new google.maps.InfoWindow({
                content: '<div style="padding: 10px;"><strong>' + address + '</strong><br><a href="https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address) + '" target="_blank">Ottieni indicazioni</a></div>'
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
            
        } else {
            console.error('Geocoding fallito: ' + status);
            mapElement.innerHTML = '<div style="padding: 40px; text-align: center; background: #fcf0f1; border-radius: 10px;"><p style="color: #8b0000;">Impossibile caricare la mappa. <a href="https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address) + '" target="_blank" style="color: #2271b1; font-weight: 600;">Apri in Google Maps</a></p></div>';
        }
    });
}
*/ // Fine funzione initMap disabilitata

// Inizializza quando la pagina è pronta - DISABILITATO (Ora usiamo OpenStreetMap)
/* BLOCCO DISABILITATO
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Inizializza mappa se presente
        if (document.getElementById('map')) {
            // Carica Google Maps se non è già caricato
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                var apiKey = window.wiGoogleMapsKey || ''; // Può essere impostato dal PHP
                if (apiKey) {
                    var script = document.createElement('script');
                    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&callback=initMap';
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                } else {
                    console.warn('Google Maps API key non configurata');
                }
            } else {
                initMap();
            }
        }
    });
} else {
    if (document.getElementById('map')) {
        initMap();
    }
}
*/ // Fine blocco initialization disabilitato

// Gestione scroll smooth per link interni
document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('a[href^="#"]');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            var href = this.getAttribute('href');

            // Ignora href="#" o href="#!" (placeholder links)
            if (href === '#' || href === '#!' || href.length <= 1) {
                return;
            }

            try {
                var target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch(err) {
                // Selettore non valido, ignora silenziosamente
                console.warn('Invalid selector for smooth scroll:', href);
            }
        });
    });
});
