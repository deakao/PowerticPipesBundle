var kanban;
(function($) {
  var boards = document.getElementById('myKanban');

  if(boards) {
    kanban = new jKanban({
      element:'#myKanban',
      itemAddOptions: {
        enabled: true,                                              // add a button to board for easy item creation
        content: '+',                                                // text or html content of the board button   
        class: 'kanban-title-button btn btn-default btn-xs',         // default class of the button
        footer: false                                                // position the button on footer
      },   
      buttonClick: function(el, boardId) {
        console.log(el);
        console.log(boardId);
        // create a form to enter element
        var formItem = document.createElement("form");
        formItem.setAttribute("class", "itemform");
        formItem.innerHTML =
          '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="pb-md"> <button type="submit" class="btn btn-primary btn-xs pull-right">'+mauticLang['plugin.powerticpipes.add']+'</button> <button type="button" id="CancelBtn" class="btn btn-danger btn-xs pull-right mr-xs">'+mauticLang['plugin.powerticpipes.cancel']+'</button> </div>';

        kanban.addForm(boardId, formItem);
        formItem.addEventListener("submit", function(e) {
          e.preventDefault();
          var text = e.target[0].value;
          kanban.addElement(boardId, {
            title: text
          });
          formItem.parentNode.removeChild(formItem);
        });
        document.getElementById("CancelBtn").onclick = function() {
          formItem.parentNode.removeChild(formItem);
        };
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

  $('.kanban-title-board').on('input', function(e) {
    var text = $(this).text();
    console.log(text);
  })
})(jQuery);