(function ($) {
  'use strict';

  $(function () {
    const $list = $('[data-company-order-list]');
    const $save = $('[data-company-order-save]');
    const $status = $('[data-company-order-status]');

    if (!$list.length || !$save.length || typeof nuventuresCompanyOrder === 'undefined') {
      return;
    }

    $list.sortable({
      axis: 'y',
      cancel: 'input, textarea, select, option',
      containment: 'parent',
      handle: '.nuventures-company-order__handle',
      items: '> [data-company-id]',
      placeholder: 'nuventures-company-order__placeholder',
      forcePlaceholderSize: true,
      tolerance: 'pointer',
    });

    $save.on('click', function () {
      const companyIds = $list
        .children('[data-company-id]')
        .map(function () {
          return Number($(this).data('company-id'));
        })
        .get()
        .filter(Number.isInteger);

      $save.prop('disabled', true);
      $status.removeClass('is-error is-success').text(nuventuresCompanyOrder.saving);

      $.ajax({
        url: nuventuresCompanyOrder.ajaxUrl,
        method: 'POST',
        dataType: 'json',
        data: {
          action: nuventuresCompanyOrder.action,
          nonce: nuventuresCompanyOrder.nonce,
          company_ids: companyIds,
        },
      })
        .done(function (response) {
          if (!response || !response.success) {
            const message = response && response.data && response.data.message
              ? response.data.message
              : nuventuresCompanyOrder.error;
            $status.addClass('is-error').text(message);
            return;
          }

          const message = response.data && response.data.message
            ? response.data.message
            : nuventuresCompanyOrder.saved;
          $status.addClass('is-success').text(message);
        })
        .fail(function (request) {
          const message = request.responseJSON
            && request.responseJSON.data
            && request.responseJSON.data.message
            ? request.responseJSON.data.message
            : nuventuresCompanyOrder.error;
          $status.addClass('is-error').text(message);
        })
        .always(function () {
          $save.prop('disabled', false);
        });
    });
  });
})(jQuery);
