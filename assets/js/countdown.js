/**
 * Countdown Timer JavaScript
 * Gestione del timer per il conto alla rovescia
 */

(function($) {
    'use strict';
    
    /**
     * Inizializza il countdown
     * @param {number} targetDate - Timestamp della data target in millisecondi
     */
    window.initCountdown = function(targetDate) {
        var countdownEl = document.getElementById('countdown');
        
        if (!countdownEl) {
            console.error('Elemento countdown non trovato');
            return;
        }
        
        // Funzione per aggiornare il countdown
        function updateCountdown() {
            var now = new Date().getTime();
            var distance = targetDate - now;
            
            // Se il countdown è scaduto
            if (distance < 0) {
                clearInterval(countdownInterval);
                displayExpired(countdownEl);
                return;
            }
            
            // Calcola giorni, ore, minuti, secondi
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Visualizza il countdown
            displayCountdown(countdownEl, days, hours, minutes, seconds);
        }
        
        // Primo aggiornamento immediato
        updateCountdown();
        
        // Aggiorna ogni secondo
        var countdownInterval = setInterval(updateCountdown, 1000);
    };
    
    /**
     * Visualizza il countdown
     */
    function displayCountdown(element, days, hours, minutes, seconds) {
        // Controlla se il countdown è già stato creato
        var container = element.querySelector('.countdown-container');

        if (!container) {
            // Prima volta: crea l'intera struttura HTML
            var html = '<div class="countdown-container">';
            html += createCountdownItem(days, 'Giorni', 'days');
            html += createCountdownItem(hours, 'Ore', 'hours');
            html += createCountdownItem(minutes, 'Minuti', 'minutes');
            html += createCountdownItem(seconds, 'Secondi', 'seconds');
            html += '</div>';
            element.innerHTML = html;
        } else {
            // Aggiorna solo i valori senza ricreare l'HTML
            updateCountdownValue('days', days);
            updateCountdownValue('hours', hours);
            updateCountdownValue('minutes', minutes);
            updateCountdownValue('seconds', seconds);
        }
    }
    
    /**
     * Crea un singolo elemento del countdown
     */
    function createCountdownItem(value, label, id) {
        // Aggiungi zero iniziale se necessario
        var displayValue = value < 10 ? '0' + value : value;

        return '<div class="countdown-item" data-unit="' + id + '">' +
                   '<div class="countdown-value" id="countdown-' + id + '">' + displayValue + '</div>' +
                   '<div class="countdown-label">' + label + '</div>' +
               '</div>';
    }

    /**
     * Aggiorna solo il valore di un countdown senza ricreare l'HTML
     */
    function updateCountdownValue(unit, value) {
        var element = document.getElementById('countdown-' + unit);
        if (element) {
            var displayValue = value < 10 ? '0' + value : value;
            // Aggiorna solo se il valore è cambiato (evita flash inutili)
            if (element.textContent !== displayValue.toString()) {
                element.textContent = displayValue;
            }
        }
    }
    
    /**
     * Visualizza messaggio di scadenza
     */
    function displayExpired(element) {
        element.innerHTML = '<div class="countdown-expired">' +
                                '<span class="dashicons dashicons-yes-alt"></span> ' +
                                'L\'evento è iniziato!' +
                            '</div>';
        
        // Effetto confetti (opzionale)
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }
    }
    
    /**
     * Calcola il tempo rimanente
     */
    window.getTimeRemaining = function(targetDate) {
        var now = new Date().getTime();
        var distance = targetDate - now;
        
        if (distance < 0) {
            return null;
        }
        
        return {
            total: distance,
            days: Math.floor(distance / (1000 * 60 * 60 * 24)),
            hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((distance % (1000 * 60)) / 1000)
        };
    };
    
    /**
     * Formatta la data per visualizzazione
     */
    window.formatCountdownDate = function(date) {
        var months = [
            'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
            'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'
        ];
        
        var d = new Date(date);
        var day = d.getDate();
        var month = months[d.getMonth()];
        var year = d.getFullYear();
        var hours = d.getHours();
        var minutes = d.getMinutes();
        
        // Aggiungi zero iniziale
        if (hours < 10) hours = '0' + hours;
        if (minutes < 10) minutes = '0' + minutes;
        
        return day + ' ' + month + ' ' + year + ' alle ' + hours + ':' + minutes;
    };
    
    /**
     * Countdown multipli (per preview)
     */
    window.initMultipleCountdowns = function() {
        $('.wi-countdown').each(function() {
            var $this = $(this);
            var targetDate = $this.data('target-date');
            
            if (targetDate) {
                var timestamp = new Date(targetDate).getTime();
                initCountdown(timestamp, $this[0]);
            }
        });
    };
    
    // Inizializzazione automatica
    $(document).ready(function() {
        // Se esiste una variabile globale eventDateTime, inizializza il countdown
        if (typeof eventDateTime !== 'undefined') {
            var timestamp = new Date(eventDateTime).getTime();
            initCountdown(timestamp);
        }
        
        // Inizializza countdown multipli se presenti
        if ($('.wi-countdown').length > 1) {
            initMultipleCountdowns();
        }
    });
    
})(jQuery);

/**
 * Utility per countdown compatti (badge, widget)
 */
function createCompactCountdown(targetDate, format) {
    var now = new Date().getTime();
    var distance = targetDate - now;
    
    if (distance < 0) {
        return 'Evento iniziato';
    }
    
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    
    switch(format) {
        case 'days-only':
            return days + ' giorni';
        case 'days-hours':
            return days + 'g ' + hours + 'h';
        case 'natural':
            if (days === 0) {
                return 'Oggi!';
            } else if (days === 1) {
                return 'Domani';
            } else if (days < 7) {
                return 'Tra ' + days + ' giorni';
            } else if (days < 30) {
                var weeks = Math.floor(days / 7);
                return 'Tra ' + weeks + ' settimana' + (weeks > 1 ? 'e' : '');
            } else {
                var months = Math.floor(days / 30);
                return 'Tra ' + months + ' mese' + (months > 1 ? 'i' : '');
            }
        default:
            return days + ' giorni, ' + hours + ' ore';
    }
}

/**
 * Esporta funzioni globali
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initCountdown: window.initCountdown,
        getTimeRemaining: window.getTimeRemaining,
        formatCountdownDate: window.formatCountdownDate,
        createCompactCountdown: createCompactCountdown
    };
}