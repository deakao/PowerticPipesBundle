var kanban;
(function($) {
  var boards = document.getElementById('myKanban');

  if(boards) {
    kanban = new jKanban({
      element:'#myKanban',
      itemAddOptions: {
        enabled: true,
        content: mauticLang['plugin.powerticpipes.btn_add_item'],
        class: 'kanban-title-button btn btn-success btn-block',         // default class of the button
        footer: true                                                // position the button on footer
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
          var text = e.target[0].value;
          formItem.parentNode.removeChild(formItem);

          $.post(addCardAction, {
            list_id: boardId,
            name: text,
            order: kanban.options.boards[3].item.length+1
          }, function(data){
            kanban.addElement(boardId, {
              title: text,
              id: data.id,
            });
          });
        });

        document.getElementById("CancelBtn").onclick = function() {
          formItem.parentNode.removeChild(formItem);
        };
      },
      dragendBoard: function (el) {
        var lists = $('.kanban-container')
        var post_data = {};
        lists.find('.kanban-board').each(function(index, el) {
          post_data['list_id['+index+']'] = $(el).attr('data-id').replace('id_', '');
          post_data['order['+index+']'] = index+1;
        });
        $.post(updateListSortAction, post_data);
      },
      dragendEl: function (item) {
        var elm = $(item).parents('.kanban-board');
        var post_data = {
          'list_id': elm.attr('data-id').replace('id_', ''),
        }
        elm.find('.kanban-item').each(function(index, el) {
          post_data['card_id['+index+']'] = $(el).attr('data-eid');
          post_data['order['+index+']'] = index+1;
        });
        $.post(updateCardSortAction, post_data);
      },
      buttonRemoveClick: function(el, boardId) {
        if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_list'])){
          $(el).parents('.kanban-board').fadeOut(function(){
            $(this).remove();
          });
          $.get(removeListAction+'/'+boardId.replace('id_', ''));
        }
        
      },
      
      boards: kanban_content
    });


    $('#add_list').on('click', function(e) {
      e.preventDefault();
      var elm = $(this)
      var post_data = {
        'order': (kanban.options.boards.length+1)
      }
      $.post(elm.attr('href'), post_data, function(data){
        kanban.addBoards([
          {
            id: data.list_id,
            title: data.name,
          }]
        );
      }, 'json')
      
    });
  }

  $('body').on('input', '.kanban-title-board', function(e) {
    var text = $(this).text();
    var id = $(this).parents('.kanban-board').attr('data-id').replace('id_', '');
    var post_data = {
      'name': text,
      'list_id': id
    }
    $.post(updateListNameAction, post_data)
  });

  $('body').on('click', '.kanban-item-remove', function(e) {
    e.preventDefault();
    var elm = $(this)
    var id = elm.parents('.kanban-item').attr('data-eid');
    if(confirm(mauticLang['plugin.powerticpipes.confirm_delete_card'])){
      $.get(removeCardAction+'/'+id.replace('id_', ''));
      elm.parents('.kanban-item').fadeOut(function(){
        elm.parents('.kanban-item').remove();
      });
    }
  });
  
})(jQuery);