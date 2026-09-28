/**
 * SpamCustomerCleaner - Admin JavaScript
 */

(function(window, $) {
    'use strict';
    $ = $ || window.jQuery || window.$;
    if (!$) {
        console.error('[SpamCustomerCleaner] jQuery not found');
        return;
    }

    var allSpamItems = [];
    var filteredItems = [];
    var selectedIds = new Set();
    var currentPage = 1;
    var itemsPerPage = 50;
    var isDeleting = false;

    $(document).ready(function() {
        console.log('[SpamCustomerCleaner] Module JS loaded. AJAX URL:', typeof sccAjaxUrl !== 'undefined' ? sccAjaxUrl : 'NOT SET');
        initEvents();
    });

    function initEvents() {
        window.runScan = runScan;

        // Toggle settings accordion (delegated)
        $(document).on('click', '#toggle-settings-btn', function(e) {
            e.preventDefault();
            var $panel = $('#settings-panel-body');
            var $icon = $(this).find('i');
            if ($panel.is(':visible')) {
                $panel.slideUp();
                $icon.removeClass('icon-chevron-up').addClass('icon-chevron-down');
            } else {
                $panel.slideDown();
                $icon.removeClass('icon-chevron-down').addClass('icon-chevron-up');
            }
        });

        // Start scan button (delegated)
        $(document).on('click', '#btn-start-scan', function(e) {
            e.preventDefault();
            runScan();
        });

        // Filter input
        $('#scc-filter-input').on('keyup', function() {
            applyFilters();
        });

        // Address filter select
        $('#scc-filter-addr').on('change', function() {
            applyFilters();
        });

        // Header and global select all
        $('#select-all-checkbox, #header-select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('#select-all-checkbox').prop('checked', isChecked);
            $('#header-select-all').prop('checked', isChecked);

            if (isChecked) {
                filteredItems.forEach(function(item) {
                    selectedIds.add(item.id_customer);
                });
            } else {
                filteredItems.forEach(function(item) {
                    selectedIds.delete(item.id_customer);
                });
            }
            updateRowCheckboxes();
            updateSelectionCounts();
        });

        // Individual row checkbox change
        $(document).on('change', '.scc-row-checkbox', function() {
            var id = parseInt($(this).val(), 10);
            if ($(this).is(':checked')) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
            updateSelectionCounts();
        });

        // License activation handler
        $('#btn-activate-license').on('click', function() {
            var key = ($('#scc-license-input').val() || '').trim();
            if (!key) {
                alert((window.sccLang && sccLang.enter_license_key) || 'Veuillez saisir votre clé de licence Genisoft.');
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> ' + ((window.sccLang && sccLang.validating) || 'Validation...'));

            $.ajax({
                url: sccAjaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'activate_license',
                    license_key: key
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="icon-key"></i> ' + ((window.sccLang && sccLang.activate) || 'Activer'));
                    if (res.success) {
                        alert((window.sccLang && sccLang.license_activated) || 'Félicitations ! Votre licence PRO est activée avec succès !');
                        location.reload();
                    } else {
                        alert(res.error || (window.sccLang && sccLang.invalid_license) || 'Clé de licence invalide.');
                    }
                },
                error: function(xhr, status, err) {
                    $btn.prop('disabled', false).html('<i class="icon-key"></i> Activer');
                    alert(((window.sccLang && sccLang.server_error) || 'Erreur lors de la validation : ') + err);
                }
            });
        });

        // Delete selected modal trigger
        $('#btn-delete-selected').on('click', function() {
            if (selectedIds.size === 0) {
                alert((window.sccLang && sccLang.select_at_least_one) || 'Veuillez sélectionner au moins un compte spam à supprimer.');
                return;
            }

            if (!sccIsPro && sccRemainingFree <= 0) {
                $('#scc-upgrade-modal').modal('show');
                return;
            }

            var toDeleteCount = selectedIds.size;
            if (!sccIsPro) {
                $('#free-limit-alert').show();
                $('#free-limit-count').text(sccRemainingFree);
            }

            $('#confirm-customers-count').text(toDeleteCount);
            $('#deletion-confirm-view').show();
            $('#deletion-progress-view').hide();
            $('#deletion-completed-view').hide();
            $('#btn-modal-cancel').show();
            $('#btn-modal-confirm-delete').show();
            $('#btn-modal-close-refresh').hide();
            $('#scc-deletion-modal').modal('show');
        });

        // Confirm batch delete
        $('#btn-modal-confirm-delete').on('click', function() {
            startBatchDeletion();
        });

        // Contact spam scan
        $('#btn-scan-contact').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Analyse...');

            $.ajax({
                url: sccAjaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'scan_contact'
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="icon-search"></i> Analyser les Messages');
                    if (res.success) {
                        renderContactSpamTable(res.items);
                    }
                },
                error: function(xhr, status, err) {
                    $btn.prop('disabled', false).html('<i class="icon-search"></i> Analyser les Messages');
                    alert('Erreur : ' + err);
                }
            });
        });

        // Contact select all
        $('#contact-select-all').on('change', function() {
            $('.contact-row-cb').prop('checked', $(this).is(':checked'));
        });

        // Contact spam batch delete
        $('#btn-delete-contact-spam').on('click', function() {
            if (!sccIsPro) {
                $('#scc-upgrade-modal').modal('show');
                return;
            }
            var ids = [];
            $('.contact-row-cb:checked').each(function() {
                ids.push(parseInt($(this).val(), 10));
            });
            if (ids.length === 0) {
                alert('Veuillez sélectionner au moins un message spam à supprimer.');
                return;
            }
            if (!confirm('Voulez-vous supprimer définitivement ces ' + ids.length + ' messages spams ?')) {
                return;
            }
            $.ajax({
                url: sccAjaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'delete_contact_batch',
                    thread_ids: ids
                },
                success: function(res) {
                    if (res.success) {
                        alert(res.deleted_count + ' messages spams supprimés avec succès.');
                        $('#btn-scan-contact').click();
                    } else if (res.upgrade_required) {
                        $('#scc-upgrade-modal').modal('show');
                    } else {
                        alert(res.error || 'Erreur inconnue');
                    }
                }
            });
        });

        // Delete single item from table row
        $(document).on('click', '.btn-delete-single', function() {
            var id = parseInt($(this).data('id'), 10);
            var name = $(this).data('name');
            if (confirm('Voulez-vous supprimer définitivement ce compte spam (' + name + ') et son adresse ?')) {
                deleteSingleCustomer(id);
            }
        });
    }

    function runScan() {
        window.runScan = runScan;
        $('#scan-placeholder').hide();
        $('#scan-table-wrapper').hide();
        $('#scan-loading').show();
        $('#btn-start-scan').prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Scan en cours...');

        $.ajax({
            url: sccAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'scan'
            },
            success: function(res) {
                $('#scan-loading').hide();
                $('#btn-start-scan').prop('disabled', false).html('<i class="icon-search"></i> Relancer le Scan');

                if (res.success) {
                    $('#kpi-spam').text(res.spam_count);
                    $('#kpi-clean').text(res.clean_count);
                    $('#kpi-addr').text(res.spam_with_addr);
                    $('#scan-badge-count').text(res.spam_count);

                    allSpamItems = res.items;
                    selectedIds = new Set();
                    allSpamItems.forEach(function(item) {
                        selectedIds.add(item.id_customer);
                    });

                    $('#scan-table-wrapper').show();
                    applyFilters();
                } else {
                    alert('Erreur lors du scan : ' + (res.error || 'Erreur inconnue'));
                    $('#scan-placeholder').show();
                }
            },
            error: function(xhr, status, error) {
                $('#scan-loading').hide();
                $('#btn-start-scan').prop('disabled', false).html('<i class="icon-search"></i> Lancer le Scan Complet');
                $('#scan-placeholder').show();
                alert('Erreur serveur lors de la communication AJAX : ' + error);
            }
        });
    }

    function applyFilters() {
        var query = ($('#scc-filter-input').val() || '').toLowerCase().trim();
        var addrFilter = $('#scc-filter-addr').val();

        filteredItems = allSpamItems.filter(function(item) {
            // Text search
            if (query.length > 0) {
                var matchName = (item.firstname + ' ' + item.lastname).toLowerCase().indexOf(query) !== -1;
                var matchEmail = item.email.toLowerCase().indexOf(query) !== -1;
                var matchId = item.id_customer.toString().indexOf(query) !== -1;
                var matchReasons = item.reasons.join(' ').toLowerCase().indexOf(query) !== -1;
                if (!matchName && !matchEmail && !matchId && !matchReasons) {
                    return false;
                }
            }

            // Address filter
            if (addrFilter === 'with_addr' && item.nb_addr === 0) {
                return false;
            }
            if (addrFilter === 'no_addr' && item.nb_addr > 0) {
                return false;
            }

            return true;
        });

        currentPage = 1;
        renderTable();
        updateSelectionCounts();
    }

    function renderTable() {
        var $tbody = $('#scc-table-body');
        $tbody.empty();

        if (filteredItems.length === 0) {
            $tbody.html('<tr><td colspan="8" class="text-center text-muted" style="padding: 30px;">Aucun compte ne correspond à ces critères.</td></tr>');
            $('#scc-pagination').empty();
            $('#pagination-info').text('0 résultat');
            return;
        }

        var total = filteredItems.length;
        var totalPages = Math.ceil(total / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        var start = (currentPage - 1) * itemsPerPage;
        var end = Math.min(start + itemsPerPage, total);
        var pageItems = filteredItems.slice(start, end);

        pageItems.forEach(function(item) {
            var isChecked = selectedIds.has(item.id_customer);
            var addrBadge = item.nb_addr > 0 
                ? '<span class="badge-addr badge-addr-yes"><i class="icon-map-marker"></i> Oui (' + item.nb_addr + ')</span>' 
                : '<span class="badge-addr badge-addr-no">Non (0)</span>';

            var reasonsBadges = '';
            item.reasons.forEach(function(r) {
                reasonsBadges += '<span class="badge-reason">' + escapeHtml(r) + '</span> ';
            });

            var rowHtml = '<tr>' +
                '<td><input type="checkbox" class="scc-row-checkbox" value="' + item.id_customer + '" ' + (isChecked ? 'checked' : '') + '></td>' +
                '<td><strong>#' + item.id_customer + '</strong></td>' +
                '<td>' + escapeHtml(item.firstname + ' ' + item.lastname) + '</td>' +
                '<td><code>' + escapeHtml(item.email) + '</code></td>' +
                '<td><small class="text-muted">' + escapeHtml(item.date_add) + '</small></td>' +
                '<td class="text-center">' + addrBadge + '</td>' +
                '<td>' + reasonsBadges + '</td>' +
                '<td class="text-right">' +
                    '<button type="button" class="btn btn-default btn-xs btn-delete-single" data-id="' + item.id_customer + '" data-name="' + escapeHtml(item.firstname + ' ' + item.lastname) + '" title="Supprimer">' +
                        '<i class="icon-trash text-danger"></i>' +
                    '</button>' +
                '</td>' +
            '</tr>';
            $tbody.append(rowHtml);
        });

        $('#pagination-info').text('Affichage de ' + (start + 1) + ' à ' + end + ' sur ' + total + ' spams trouvés');
        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        var $pag = $('#scc-pagination');
        $pag.empty();

        if (totalPages <= 1) return;

        // Previous
        var prevDisabled = currentPage === 1 ? 'disabled' : '';
        $pag.append('<li class="' + prevDisabled + '"><a href="javascript:void(0);" data-page="' + (currentPage - 1) + '">&laquo;</a></li>');

        var maxPagesToShow = 7;
        var startPage = Math.max(1, currentPage - 3);
        var endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        if (endPage - startPage < maxPagesToShow - 1) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (var p = startPage; p <= endPage; p++) {
            var activeClass = p === currentPage ? 'active' : '';
            $pag.append('<li class="' + activeClass + '"><a href="javascript:void(0);" data-page="' + p + '">' + p + '</a></li>');
        }

        // Next
        var nextDisabled = currentPage === totalPages ? 'disabled' : '';
        $pag.append('<li class="' + nextDisabled + '"><a href="javascript:void(0);" data-page="' + (currentPage + 1) + '">&raquo;</a></li>');

        $pag.find('a').on('click', function() {
            var targetPage = parseInt($(this).data('page'), 10);
            if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
                currentPage = targetPage;
                renderTable();
            }
        });
    }

    function updateRowCheckboxes() {
        $('.scc-row-checkbox').each(function() {
            var id = parseInt($(this).val(), 10);
            $(this).prop('checked', selectedIds.has(id));
        });
    }

    function updateSelectionCounts() {
        var count = selectedIds.size;
        $('#selected-count').text(count);
        $('#scan-summary-text').text(count + ' / ' + allSpamItems.length + ' spams sélectionnés');

        var allFilteredSelected = filteredItems.length > 0 && filteredItems.every(function(item) {
            return selectedIds.has(item.id_customer);
        });
        $('#select-all-checkbox, #header-select-all').prop('checked', allFilteredSelected);
    }

    function startBatchDeletion() {
        isDeleting = true;
        var idsToDelete = Array.from(selectedIds);
        var totalToDelete = idsToDelete.length;
        var batchSize = sccBatchSize || 250;

        // In free mode, limit deletion to remaining free quota
        if (!sccIsPro) {
            if (sccRemainingFree <= 0) {
                $('#scc-deletion-modal').modal('hide');
                $('#scc-upgrade-modal').modal('show');
                isDeleting = false;
                return;
            }
            if (idsToDelete.length > sccRemainingFree) {
                idsToDelete = idsToDelete.slice(0, sccRemainingFree);
                totalToDelete = idsToDelete.length;
            }
        }

        var batches = [];
        for (var i = 0; i < totalToDelete; i += batchSize) {
            batches.push(idsToDelete.slice(i, i + batchSize));
        }

        var totalBatches = batches.length;
        var currentBatchIndex = 0;
        var totalDeletedCustomers = 0;
        var totalDeletedAddresses = 0;
        var totalDeletedCarts = 0;
        var deleteAddresses = $('#opt-delete-addresses').is(':checked') ? 1 : 0;

        $('#deletion-confirm-view').hide();
        $('#deletion-progress-view').show();
        $('#btn-modal-cancel').hide();
        $('#btn-modal-confirm-delete').hide();

        var $log = $('#deletion-log');
        $log.empty().append('<div>[' + ((window.sccLang && sccLang.starting) || 'Démarrage') + '] ' + ((window.sccLang && sccLang.preparing) || 'Préparation de') + ' ' + totalBatches + ' ' + ((window.sccLang && sccLang.lots) || 'lots') + ' (' + totalToDelete + ' ' + ((window.sccLang && sccLang.accounts) || 'comptes') + ')...</div>');

        function processNextBatch() {
            if (currentBatchIndex >= totalBatches) {
                // Done
                finishDeletion(totalDeletedCustomers, totalDeletedAddresses, totalDeletedCarts);
                return;
            }

            var batch = batches[currentBatchIndex];
            var batchNumber = currentBatchIndex + 1;
            var pct = Math.round((currentBatchIndex / totalBatches) * 100);

            $('#scc-progress-bar').css('width', pct + '%').text(pct + '%');
            $('#progress-status-text').text(((window.sccLang && sccLang.lot) || 'Lot') + ' ' + batchNumber + ' ' + ((window.sccLang && sccLang.of) || 'sur') + ' ' + totalBatches + '...');
            $('#progress-deleted-count').text(totalDeletedCustomers + ' / ' + totalToDelete + ' ' + ((window.sccLang && sccLang.deleted) || 'supprimés'));

            $.ajax({
                url: sccAjaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'delete_batch',
                    customer_ids: batch,
                    delete_addresses: deleteAddresses
                },
                success: function(res) {
                    if (res && res.success) {
                        totalDeletedCustomers += (res.customers_deleted || 0);
                        totalDeletedAddresses += (res.addresses_deleted || 0);
                        totalDeletedCarts += (res.carts_deleted || 0);

                        if (res.remaining_free !== undefined) {
                            sccRemainingFree = res.remaining_free;
                        }

                        var completedPct = Math.round((batchNumber / totalBatches) * 100);
                        $('#scc-progress-bar').css('width', completedPct + '%').text(completedPct + '%');
                        $('#progress-deleted-count').text(totalDeletedCustomers + ' / ' + totalToDelete + ' ' + ((window.sccLang && sccLang.deleted) || 'supprimés'));

                        $log.append('<div>✓ ' + ((window.sccLang && sccLang.lot) || 'Lot') + ' ' + batchNumber + '/' + totalBatches + ' ' + ((window.sccLang && sccLang.processed) || 'traité :') + ' ' + res.customers_deleted + ' ' + ((window.sccLang && sccLang.clients) || 'clients,') + ' ' + res.addresses_deleted + ' ' + ((window.sccLang && sccLang.addresses_deleted) || 'adresses supprimées.') + '</div>');
                        $log.scrollTop($log[0].scrollHeight);

                        if (!sccIsPro && sccRemainingFree <= 0 && currentBatchIndex + 1 < totalBatches) {
                            $log.append('<div style="color: #f59e0b;">⭐ ' + ((window.sccLang && sccLang.free_limit_reached) || 'Limite de la version gratuite atteinte') + ' (' + totalDeletedCustomers + ' ' + ((window.sccLang && sccLang.cleaned_test_accounts) || 'comptes nettoyés') + ').</div>');
                            setTimeout(function() {
                                $('#scc-deletion-modal').modal('hide');
                                $('#scc-upgrade-modal').modal('show');
                            }, 1500);
                            finishDeletion(totalDeletedCustomers, totalDeletedAddresses, totalDeletedCarts);
                            return;
                        }

                        currentBatchIndex++;
                        processNextBatch();
                    } else if (res && res.upgrade_required) {
                        sccRemainingFree = 0;
                        $log.append('<div style="color: #f59e0b;">⭐ ' + (res.error || (window.sccLang && sccLang.free_limit_reached) || 'Limite de la version gratuite atteinte.') + '</div>');
                        setTimeout(function() {
                            $('#scc-deletion-modal').modal('hide');
                            $('#scc-upgrade-modal').modal('show');
                        }, 1500);
                        finishDeletion(totalDeletedCustomers, totalDeletedAddresses, totalDeletedCarts);
                    } else {
                        var errMsg = (res && res.error) ? res.error : 'Erreur inconnue';
                        $log.append('<div style="color: #ff5555;">✗ Erreur sur le lot ' + batchNumber + ' : ' + errMsg + '</div>');
                        alert('Erreur lors du traitement du lot ' + batchNumber + ' : ' + errMsg);
                        finishDeletion(totalDeletedCustomers, totalDeletedAddresses, totalDeletedCarts);
                    }
                },
                error: function(xhr, status, error) {
                    $log.append('<div style="color: #ff5555;">✗ Erreur réseau sur le lot ' + batchNumber + ' : ' + error + '</div>');
                    alert('Erreur réseau sur le lot ' + batchNumber + ' : ' + error);
                    finishDeletion(totalDeletedCustomers, totalDeletedAddresses, totalDeletedCarts);
                }
            });
        }

        processNextBatch();
    }

    function finishDeletion(customersDeleted, addressesDeleted, cartsDeleted) {
        isDeleting = false;
        $('#scc-progress-bar').css('width', '100%').text('100%');
        $('#progress-status-text').text((window.sccLang && sccLang.done) || 'Terminé !');
        $('#progress-deleted-count').text(customersDeleted + ' ' + ((window.sccLang && sccLang.accounts_deleted) || 'comptes supprimés'));

        $('#deletion-progress-view').hide();
        $('#deletion-completed-view').show();
        $('#deletion-success-summary').html(
            '<strong>' + customersDeleted + '</strong> ' + ((window.sccLang && sccLang.fake_accounts_deleted) || 'faux comptes clients spam supprimés.') + '<br>' +
            '<strong>' + addressesDeleted + '</strong> ' + ((window.sccLang && sccLang.spam_addresses_deleted) || 'adresses postales de spam associées supprimées.') + '<br>' +
            '<strong>' + cartsDeleted + '</strong> ' + ((window.sccLang && sccLang.orphan_carts_cleaned) || 'paniers orphelins nettoyés.') + '<br>' +
            '<span class="text-success"><i class="icon-shield"></i> ' + ((window.sccLang && sccLang.safe_guarantee) || 'Vos vrais clients et vos commandes restent intacts.') + '</span>'
        );

        $('#btn-modal-close-refresh').show();
    }

    function deleteSingleCustomer(idCustomer) {
        var deleteAddresses = $('#opt-delete-addresses').is(':checked') ? 1 : 0;
        $.ajax({
            url: sccAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'delete_single',
                id_customer: idCustomer,
                delete_addresses: deleteAddresses
            },
            success: function(res) {
                if (res.success) {
                    // Remove from local arrays
                    allSpamItems = allSpamItems.filter(function(i) { return i.id_customer !== idCustomer; });
                    selectedIds.delete(idCustomer);
                    applyFilters();

                    // Update KPIs
                    var currentSpam = parseInt($('#kpi-spam').text(), 10) || 0;
                    if (currentSpam > 0) $('#kpi-spam').text(currentSpam - 1);
                    var currentTotal = parseInt($('#kpi-total').text(), 10) || 0;
                    if (currentTotal > 0) $('#kpi-total').text(currentTotal - 1);
                } else if (res.upgrade_required) {
                    $('#scc-upgrade-modal').modal('show');
                } else {
                    alert('Erreur lors de la suppression : ' + (res.error || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                alert('Erreur réseau : ' + error);
            }
        });
    }

    function renderContactSpamTable(items) {
        var $wrapper = $('#contact-spam-table-wrapper');
        var $tbody = $('#contact-spam-tbody');
        $tbody.empty();

        if (!items || items.length === 0) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">Aucun message spam détecté dans vos formulaires de contact.</td></tr>');
            $wrapper.show();
            return;
        }

        items.forEach(function(item) {
            var snippet = (item.message || '').substring(0, 140) + '...';
            var html = '<tr>' +
                '<td><input type="checkbox" class="contact-row-cb" value="' + item.id_customer_thread + '" checked></td>' +
                '<td><strong>#' + item.id_customer_thread + '</strong></td>' +
                '<td><code>' + escapeHtml(item.email) + '</code></td>' +
                '<td><small class="text-muted">' + escapeHtml(item.date_add) + '</small></td>' +
                '<td><span style="font-size: 12px; color: #dc2626;">' + escapeHtml(snippet) + '</span></td>' +
            '</tr>';
            $tbody.append(html);
        });

        $wrapper.show();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})(window, window.jQuery || window.$);
