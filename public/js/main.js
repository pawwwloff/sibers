$(document).on('click', '#list-tab a', function (e) {
  e.preventDefault()
  $(this).tab('show')
})

$(document).on('click', 'a.table-sort', function (e) {
  e.preventDefault()
  var form = $('#sort-form');
  form.find('input[name="sortBy"]').val($(this).attr('data-sort-by'));
  form.submit();
})