var kanban;

Mautic.composePipeWatcher = function(container) {


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

        click: function(el){
          var elm = mQuery(el);
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

            jQuery.post(addCardAction, {
              list_id: boardId,
              name: text,
              order: itens.length+1
            }, function(data){
              kanban.addElement(boardId, {
                title: text,
                id: data.id,
                date: data.date
              });
            });
          });

          document.getElementById("CancelBtn").onclick = function() {
            formItem.parentNode.removeChild(formItem);
          };
        },
        dragendBoard: function (el) {
          var lists = jQuery('.kanban-container')
          var post_data = {};
          lists.find('.kanban-board').each(function(index, el) {
            post_data['list_id['+index+']'] = jQuery(el).attr('data-id').replace('id_', '');
            post_data['order['+index+']'] = index+1;
          });
          jQuery.post(updateListSortAction, post_data);
        },
        dragendEl: function (item) {
          console.log(item);
          var elm = jQuery(item).parents('.kanban-board');
          var post_data = {
            'list_id': elm.attr('data-id').replace('id_', ''),
          }
          elm.find('.kanban-item').each(function(index, el) {
            post_data['card_id['+index+']'] = jQuery(el).attr('data-eid');
            post_data['order['+index+']'] = index+1;
          });
          jQuery.post(updateCardSortAction, post_data);
          var utc = new Date().toJSON();
          jQuery(item).find('.kanban-item-date').text(utc.slice(0,10).split('-').reverse().join('/')+' '+utc.slice(11,19));

        },
        buttonRemoveClick: function(el, boardId) {
          if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_list'])){
            jQuery(el).parents('.kanban-board').fadeOut(function(){
              jQuery(this).remove();
            });
            jQuery.get(removeListAction+'/'+boardId.replace('id_', ''));
          }
          
        },
        
        boards: kanban_content
      });

      jQuery('#add_list').on('click', function(e) {
          e.preventDefault();
          var elm = jQuery(this)
          var post_data = {
            'order': (kanban.options.boards.length+1)
          }
          
          jQuery.post(elm.attr('href'), post_data, function(data){
            elm.find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-plus');
            kanban.addBoards([
              {
                id: data.list_id,
                title: data.name,
              }]
            );
          }, 'json')
          
        });

      jQuery('body').on('click', '#cards_buttons_apply', function (e){
        e.preventDefault();
        var form = jQuery('form[name=cards]');
        var elm = jQuery(this);
        elm.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
        jQuery.post(form.attr('action'), form.serialize(), function(data){
          elm.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
          var item = jQuery('[data-eid='+data.id+']')
          item.find('.kanban-item-title').text(data.name);
        }, 'json')
      });

      jQuery('body').on('click', '#cards_buttons_save', function (e){
        e.preventDefault();
        var form = jQuery('form[name=cards]');
        var elm = jQuery(this);
        elm.find('i').removeClass('fa-check').addClass('fa-spinner fa-spin');
        jQuery.post(form.attr('action'), form.serialize(), function(data){
          elm.find('i').removeClass('fa-spinner fa-spin').addClass('fa-check');
          var item = jQuery('[data-eid='+data.id+']')
          item.find('.kanban-item-title').text(data.name);
          jQuery('#MauticSharedModal').modal('hide');
        }, 'json')
      });

      jQuery('body').on('click', '#cards_buttons_cancel', function(e){
        e.preventDefault();
        jQuery('#MauticSharedModal').modal('hide');
      })

      jQuery('body').on('input', '.kanban-title-board', function(e) {
        var text = jQuery(this).text();
        var id = jQuery(this).parents('.kanban-board').attr('data-id').replace('id_', '');
        var post_data = {
          'name': text,
          'list_id': id
        }
        jQuery.post(updateListNameAction, post_data)
      });

      jQuery('body').on('click', '.kanban-item-remove', function(e) {
        e.preventDefault();
        var elm = jQuery(this)
        var id = elm.parents('.kanban-item').attr('data-eid');
        if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_card'])){
          jQuery.get(removeCardAction+'/'+id.replace('id_', ''));
          elm.parents('.kanban-item').fadeOut(function(){
            elm.parents('.kanban-item').remove();
          });
        }
      });
}