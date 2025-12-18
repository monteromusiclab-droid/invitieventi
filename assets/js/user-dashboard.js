/**
 * User Dashboard JavaScript
 * Gestisce interazioni dashboard personale frontend
 */

(function($) {
    'use strict';

    const UserDashboard = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Pulsante "Vedi Lista Ospiti"
            $(document).on('click', '.wi-btn-view-guests', (e) => {
                e.preventDefault();
                const inviteId = $(e.currentTarget).data('invite-id');
                this.openGuestsModal(inviteId);
            });

            // Chiudi modal
            $(document).on('click', '.wi-modal-close', (e) => {
                const inviteId = $(e.currentTarget).data('invite-id');
                this.closeGuestsModal(inviteId);
            });

            // Chiudi modal cliccando fuori
            $(document).on('click', '.wi-guests-modal', (e) => {
                if ($(e.target).hasClass('wi-guests-modal')) {
                    const inviteId = $(e.target).closest('.wi-guests-modal').attr('id').replace('guests-modal-', '');
                    this.closeGuestsModal(inviteId);
                }
            });

            // Export CSV
            $(document).on('click', '.wi-btn-export-csv', (e) => {
                e.preventDefault();
                const inviteId = $(e.currentTarget).data('invite-id');
                this.exportCSV(inviteId);
            });

            // ESC per chiudere modal
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    $('.wi-guests-modal:visible').each(function() {
                        const inviteId = $(this).attr('id').replace('guests-modal-', '');
                        UserDashboard.closeGuestsModal(inviteId);
                    });
                }
            });
        },

        openGuestsModal: function(inviteId) {
            const modal = $(`#guests-modal-${inviteId}`);

            if (modal.length === 0) {
                console.error('Modal not found for invite:', inviteId);
                return;
            }

            // Mostra modal
            modal.fadeIn(300);
            $('body').css('overflow', 'hidden');

            // Carica ospiti se non già caricati
            const guestsList = modal.find('.wi-guests-list');
            if (guestsList.children().length === 0) {
                this.loadGuests(inviteId);
            }
        },

        closeGuestsModal: function(inviteId) {
            const modal = $(`#guests-modal-${inviteId}`);
            modal.fadeOut(300);
            $('body').css('overflow', '');
        },

        loadGuests: function(inviteId) {
            const modal = $(`#guests-modal-${inviteId}`);
            const loadingEl = modal.find('.wi-guests-loading');
            const listEl = modal.find('.wi-guests-list');

            loadingEl.show();
            listEl.hide().html('');

            $.ajax({
                url: wiUserDashboard.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_get_invite_guests',
                    nonce: wiUserDashboard.nonce,
                    invite_id: inviteId
                },
                success: (response) => {
                    loadingEl.hide();

                    if (response.success) {
                        this.renderGuestsList(listEl, response.data);
                        listEl.fadeIn(300);
                    } else {
                        listEl.html(`
                            <div class="wi-error-message">
                                <p>${response.data.message || 'Errore nel caricamento degli ospiti'}</p>
                            </div>
                        `).fadeIn(300);
                    }
                },
                error: () => {
                    loadingEl.hide();
                    listEl.html(`
                        <div class="wi-error-message">
                            <p>Errore di connessione. Riprova.</p>
                        </div>
                    `).fadeIn(300);
                }
            });
        },

        renderGuestsList: function(container, guests) {
            if (guests.length === 0) {
                container.html(`
                    <div class="wi-no-guests">
                        <p>Nessun ospite ha ancora confermato la presenza.</p>
                    </div>
                `);
                return;
            }

            let html = '<div class="wi-guests-table">';
            html += '<table class="wi-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>Ospite</th>';
            html += '<th>Email</th>';
            html += '<th>Telefono</th>';
            html += '<th>Stato</th>';
            html += '<th>N. Ospiti</th>';
            html += '<th>Menù</th>';
            html += '<th>Data Risposta</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            guests.forEach((guest) => {
                const statusClass = guest.status === 'attending' ? 'confirmed' :
                                  guest.status === 'not_attending' ? 'declined' : 'maybe';
                const statusText = guest.status === 'attending' ? '✅ Confermato' :
                                 guest.status === 'not_attending' ? '❌ Rifiutato' : '❓ Forse';

                html += '<tr>';
                html += `<td><strong>${this.escapeHtml(guest.guest_name)}</strong></td>`;
                html += `<td>${this.escapeHtml(guest.guest_email)}</td>`;
                html += `<td>${guest.guest_phone ? this.escapeHtml(guest.guest_phone) : '-'}</td>`;
                html += `<td><span class="wi-status-badge wi-status-${statusClass}">${statusText}</span></td>`;
                html += `<td>${guest.num_guests}</td>`;
                html += `<td>${guest.menu_choice ? this.escapeHtml(guest.menu_choice) : '-'}</td>`;
                html += `<td>${this.formatDate(guest.responded_at)}</td>`;
                html += '</tr>';

                // Note row (se presenti)
                if (guest.notes) {
                    html += '<tr class="wi-notes-row">';
                    html += `<td colspan="7">`;
                    html += `<div class="wi-guest-notes">`;
                    html += `<strong>Note:</strong> ${this.escapeHtml(guest.notes)}`;
                    html += `</div>`;
                    html += `</td>`;
                    html += '</tr>';
                }
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            container.html(html);
        },

        exportCSV: function(inviteId) {
            // Crea un form temporaneo e submit
            const form = $('<form>', {
                method: 'POST',
                action: wiUserDashboard.ajax_url
            });

            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'wi_export_guests_csv'
            }));

            form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: wiUserDashboard.nonce
            }));

            form.append($('<input>', {
                type: 'hidden',
                name: 'invite_id',
                value: inviteId
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        },

        formatDate: function(dateStr) {
            const date = new Date(dateStr);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },

        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, (m) => map[m]);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.wi-user-dashboard').length) {
            console.log('User Dashboard found, initializing...');
            UserDashboard.init();
        }
    });

})(jQuery);
