var kanban;
Mautic.composePipeWatcher = function(container) {
  createKanban();
};
Mautic.composePipeCreate = function(container) {

  createKanban();
  mQuery('body').on('click', '.kanban-board-nav-next', function(e) {
    e.preventDefault();
    var elm = mQuery(this);
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
                id: data.list_id,
                title: data.name,
              }]
            );
          }, 'json')
          
        });

      mQuery('body').on('click', '#cards_buttons_apply', function (e){
        e.preventDefault();
        var form = mQuery('form[name=cards]');
        var elm = mQuery(this);
        elm.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
        mQuery.post(form.attr('action'), form.serialize(), function(data){
          elm.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
          var item = mQuery('[data-eid='+data.id+']');
          item.find('.kanban-item-lead').remove();
          if(typeof data.lead != 'undefined' && typeof data.lead.name != 'undefined'){
            item.find('.kanban-item-creator').after('<div class="kanban-item-lead text-right small"><b>Contato:</b> <span>'+data.lead.name+'</span></div>');
          }
          item.find('.kanban-item-title').text(data.name);
        }, 'json')
      });

      mQuery('body').on('click', '#cards_buttons_save', function (e){
        e.preventDefault();
        var form = mQuery('form[name=cards]');
        var elm = mQuery(this);
        elm.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
        mQuery.post(form.attr('action'), form.serialize(), function(data){
          elm.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
          var item = mQuery('[data-eid='+data.id+']')
          item.find('.kanban-item-title').text(data.name);
          item.find('.kanban-item-lead').remove();
          if(typeof data.lead != 'undefined' && typeof data.lead.name != 'undefined'){
            item.find('.kanban-item-creator').after('<div class="kanban-item-lead text-right small"><b>Contato:</b> <span>'+data.lead.name+'</span></div>');
          }
          mQuery('#MauticSharedModal').modal('hide');
        }, 'json')
      });

      mQuery('body').on('click', '#cards_buttons_cancel', function(e){
        e.preventDefault();
        mQuery('#MauticSharedModal').modal('hide');
      })

      mQuery('body').on('input', '.kanban-title-board', function(e) {
        var text = mQuery(this).text();
        var id = mQuery(this).parents('.kanban-board').attr('data-id').replace('id_', '');
        var post_data = {
          'name': text,
          'list_id': id
        }
        mQuery.post(updateListNameAction, post_data)
      });

      mQuery('body').on('click', '.kanban-item-remove', function(e) {
        e.preventDefault();
        var elm = mQuery(this)
        var id = elm.parents('.kanban-item').attr('data-eid');
        if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_card'])){
          mQuery.get(removeCardAction+'/'+id.replace('id_', ''));
          elm.parents('.kanban-item').fadeOut(function(){
            elm.parents('.kanban-item').remove();
          });
        }
      });
}

function createKanban(){

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
              kanban.addElement(boardId, {
                title: text,
                id: data.id,
                date: data.date,
                creator: data.creator,
              });
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
            mQuery(el).parents('.kanban-board').fadeOut(function(){
              mQuery(this).remove();
            });
            mQuery.get(removeListAction+'/'+boardId.replace('id_', ''));
          }
          
        },
        
        boards: kanban_content
      });
}