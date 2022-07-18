var kanban;
var edit_powerticPipesListKey;
function number_format (number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

Mautic.composePipeWatcher = function(container) {
  createKanban();
};
Mautic.composePipeCreate = function(container) {

  createKanban();
  mQuery('body').on('click', '.kanban-board-nav-next', function(e) {
    e.preventDefault();
    var elm = mQuery(this);
    if(elm.hasClass('disabled')) {
      return;
    }
    var boardKey = elm.attr('data-boardKey');
    var list = kanban_content[boardKey];
    var list_id = list['id'].replace('id_', '');
    var offset = list['current_page'] * list['per_page'];
    var icon_orgin = elm.html();
    elm.html('<i class="fa fa-spinner fa-spin"></i>');
    mQuery.getJSON(mauticAjaxUrl+'?action=plugin:powerticPipes:cardsList&list_id='+list_id+'&offset='+offset+'&per_page='+list['per_page'], function(data){
      elm.html(icon_orgin);
      kanban_content[boardKey]['current_page']++;
      kanban_content[boardKey]['item'] = data.cards;
      kanban=null;
      mQuery('#myKanban').html('');
      createKanban();
    });
  });
  mQuery('body').on('click', '.kanban-board-nav-prev', function(e) {
    e.preventDefault();
    var elm = mQuery(this);
    if(elm.hasClass('disabled')) {
      return;
    }
    var boardKey = elm.attr('data-boardKey');
    var list = kanban_content[boardKey];
    var list_id = list['id'].replace('id_', '');
    kanban_content[boardKey]['current_page']--;
    var offset = (kanban_content[boardKey]['current_page'] -1) * list['per_page'];
    if(kanban_content[boardKey]['current_page'] == 1){
      offset = 0;
    }
    var icon_orgin = elm.html();
    elm.html('<i class="fa fa-spinner fa-spin"></i>');
    mQuery.getJSON(mauticAjaxUrl+'?action=plugin:powerticPipes:cardsList&list_id='+list_id+'&offset='+offset+'&per_page='+list['per_page'], function(data){
      elm.html(icon_orgin);
      kanban_content[boardKey]['item'] = data.cards;
      kanban=null;
      mQuery('#myKanban').html('');
      createKanban();
    });
  });

  mQuery('body').on('click', '.kanban-item-title', function(e){
    e.preventDefault();
    var elm = mQuery(this).parents('.kanban-item');
    mQuery('#MauticSharedModal .loading-placeholder').show();
    mQuery('#MauticSharedModal .modal-body-content').html('');
    mQuery('#MauticSharedModal-label').text(mauticLang['plugin.powerticpipes.edit_card']);
    mQuery('#MauticSharedModal').modal('show');
    edit_powerticPipesListKey = elm.parents('.kanban-board').attr('data-boardkey');
    fetch(editCardAction+'/'+elm.data('eid')).then(function(response) {
      response.text().then(function(data) {
        mQuery('#MauticSharedModal .loading-placeholder').hide();
        mQuery('#MauticSharedModal .modal-body-content').html(data);
        mQuery('#MauticSharedModal #cards_lead').chosen({width: '100%'}).ajaxChosen({
          type: 'GET',
          url: mauticAjaxUrl+'?action=plugin:powerticPipes:searchContact',
          dataType: 'json',
        }, function (data) {
            var results = [];
            mQuery.each( data.leads, function(i,  item ) {
              results.push({ value: item.value, text: item.label });
            });
            return results;
        });
      });
    });
  });

  mQuery('body').on('click', '#add_list', function(e){
    e.preventDefault();
    var elm = mQuery(this)
    var post_data = {
      'order': (kanban.options.boards.length+1)
    }
    
    mQuery.post(elm.attr('href'), post_data, function(data){
      elm.find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-plus');
      kanban.addBoards([
        {
          id: 'id_'+data.list_id,
          title: data.name,
          current_page: data.current_page,
          per_page: data.per_page,
          total_items: data.total_items,
          total_pages: data.total_pages,
          total_value: data.total_value,
          item: []
        }]
      );
    }, 'json');
  });

  mQuery('body').on('click', '#cards_buttons_apply', function (e){
    e.preventDefault();
    var form = mQuery('form[name=cards]');
    var elm = mQuery(this);
    elm.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
    mQuery.post(form.attr('action'), form.serialize(), function(data){
      elm.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
      kanban_content[edit_powerticPipesListKey]['total_value'] = data.list_total_value;
      reloadList();
    }, 'json')
  });

  mQuery('body').on('click', '#cards_buttons_save', function (e){
    e.preventDefault();
    var form = mQuery('form[name=cards]');
    var elm = mQuery(this);
    elm.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
    mQuery.post(form.attr('action'), form.serialize(), function(data){
      elm.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
      kanban_content[edit_powerticPipesListKey]['total_value'] = data.list_total_value;
      reloadList();
      mQuery('#MauticSharedModal').modal('hide');
    }, 'json')
  });

  mQuery('body').on('click', '#cards_buttons_cancel', function(e){
    e.preventDefault();
    mQuery('#MauticSharedModal').modal('hide');
  });
  mQuery('body').on('click', '.kanban-item-lead-contact', function(e){
    e.preventDefault();
    var elm = mQuery(this);
    window.location.href = elm.attr('href');
  });

  mQuery('body').on('input', '.kanban-title-board', function(e) {
    var text = mQuery(this).text();
    let board = mQuery(this).parents('.kanban-board');
    var id = board.attr('data-id').replace('id_', '');
    let k = board.attr('data-boardkey');
    kanban_content[k]['title'] = text;
    var post_data = {
      'name': text,
      'list_id': id
    }
    mQuery.post(updateListNameAction, post_data)
  });

  mQuery('body').on('click', '.kanban-item-remove', function(e) {
    e.preventDefault();
    var elm = mQuery(this);
    var card = elm.parents('.kanban-item');
    var id = card.attr('data-eid');
    var board = elm.parents('.kanban-board');
    var boardKey = board.attr('data-boardkey');
    var list = kanban_content[boardKey];
    if(card.attr('data-value')){
      list.total_value -= parseFloat(card.attr('data-value'));
    }
    list.total_items--;
    board.find('.total_value span').text(number_format(list.total_value, 2, ',', '.'));
    board.find('.total_items span').text(list.total_items);

    if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_card'])){
      mQuery.get(removeCardAction+'/'+id.replace('id_', ''));
      card.fadeOut(function(){
        card.remove();
      });
    }
  });
}

function reloadList() {
  var list = kanban_content[edit_powerticPipesListKey];
  var list_id = list['id'].replace('id_', '');
  var offset = list['current_page'] * list['per_page'];
  if(list['current_page'] == 1){
    offset = 0;
  }
  if(list['current_page'] == list['total_pages']){
    offset = (list['total_pages'] -1) * list['per_page'];
  }
  mQuery.getJSON(mauticAjaxUrl+'?action=plugin:powerticPipes:cardsList&list_id='+list_id+'&offset='+offset+'&per_page='+list['per_page'], function(data){
    kanban_content[edit_powerticPipesListKey]['item'] = data.cards;
    kanban=null;
    mQuery('#myKanban').html('');
    createKanban();
  });
}

function createKanban() {

  kanban = new jKanban({
    element:'#myKanban',
    itemAddOptions: {
      enabled: true,
      content: mauticLang['plugin.powerticpipes.btn_add_item'],
      class: 'kanban-title-button btn btn-success btn-block',
      footer: true
    },
    itemRemoveOptions: {
      enabled: true,
    },

    buttonClick: function(el, boardId) {
      // create a form to enter element
      var formItem = document.createElement("form");
      formItem.setAttribute("class", "itemform");
      formItem.innerHTML =
        '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="pb-md"> <button type="submit" class="btn btn-primary btn-xs pull-right">'+mauticLang['plugin.powerticpipes.add']+'</button> <button type="button" id="CancelBtn" class="btn btn-danger btn-xs pull-right mr-xs">'+mauticLang['plugin.powerticpipes.cancel']+'</button> </div>';

      kanban.addForm(boardId, formItem);
      formItem.addEventListener("submit", function(e) {
        e.preventDefault();
        var itens = kanban.getBoardElements(boardId);
        
        
        var text = e.target[0].value;
        formItem.parentNode.removeChild(formItem);

        mQuery.post(addCardAction, {
          list_id: boardId,
          name: text,
          order: itens.length+1
        }, function(data){
          mQuery(kanban.findBoard(boardId)).find('.total_items span').text(itens.length+1);
          let card = {
            title: text,
            id: data.id,
            date: data.date,
            creator: data.creator,
            lead: [],
            value: 0
          };
          kanban_content.map(function(item){
            if(item.id == boardId){
              item.item.push(card);
              item.total_items++;
            }
          });
          kanban.addElement(boardId, card);
        });
      });

      document.getElementById("CancelBtn").onclick = function() {
        formItem.parentNode.removeChild(formItem);
      };
    },
    dragendBoard: function (el) {
      var lists = mQuery('.kanban-container')
      var post_data = {};
      lists.find('.kanban-board').each(function(index, el) {
        post_data['list_id['+index+']'] = mQuery(el).attr('data-id').replace('id_', '');
        post_data['order['+index+']'] = index+1;
      });
      mQuery.post(updateListSortAction, post_data);
    },
    dragendEl: function (item) {
      console.log(item);
      var elm = mQuery(item).parents('.kanban-board');
      var post_data = {
        'list_id': elm.attr('data-id').replace('id_', ''),
      }
      elm.find('.kanban-item').each(function(index, el) {
        post_data['card_id['+index+']'] = mQuery(el).attr('data-eid');
        post_data['order['+index+']'] = index+1;
      });
      mQuery.post(updateCardSortAction, post_data);
      

    },
    buttonRemoveClick: function(el, boardId) {
      
      if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_list'])){
        let board = mQuery(el).parents('.kanban-board');
        let boardKey = board.attr('data-boardkey');
        kanban_content.splice(boardKey, 1);

        board.fadeOut(function(){
          mQuery(this).remove();
        });
        mQuery.get(removeListAction+'/'+boardId.replace('id_', ''));
      }
      
    },
    
    boards: kanban_content
  });
}