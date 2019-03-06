if ($(window).width() < 481) {
  $('#allofenchants').removeAttr('uk-accordion').removeClass('uk-accordion');
  $('#allofenchants a').prop('href','#modal-overflow').removeClass('uk-accordion-title').addClass('uk-button uk-button-default uk-width-1-1');
  $('#allofenchants a').next().attr({'id':'modal-overflow','uk-modal':''}).removeAttr('hidden aria-hidden').removeClass('uk-accordion-content').prepend('<button class="uk-modal-close-default" type="button" uk-close></button>');
  $('#NormalEnchant, #SpecialEnchant').wrapAll('<div class="uk-modal-body" uk-overflow-auto></div>');
  $('#modal-overflow').children().wrapAll('<div class="uk-modal-dialog">');
  var modal = UIkit.modal('#modal-overflow');
  UIkit.util.on('#allofenchants a', 'click', function (e) {
           e.preventDefault();
           e.target.blur();
           modal.show();
  });

  UIkit.util.on('#allofenchants label', 'click', function (e) {
           e.target.blur();
           setTimeout(function(){
             modal.hide();
           },500);
  });
  $('#scroll_to_top').addClass('uk-border-circle');
  $('#scroll_to_top').hide();
  $(window).scroll(function(){
    if ($(this).scrollTop() > $('#result').scrollTop()) {
      $('#scroll_to_top').fadeIn();
    } else {
      $('#scroll_to_top').fadeOut();
    }
  });
}

$('#searchbox').keyup(function(){
  if (!$(this).val()) {
    $('div#incremental_result label').hide();
  } else {
    $('div#incremental_result label').hide();
    $('div#incremental_result label:contains('+this.value+')').show();
  }
});

$('#equip_searchbox').keyup(function(){
  if (!$(this).val()) {
    $('div#equip_result label').hide();
  } else {
    $('div#equip_result label').hide();
    $('div#equip_result label:contains('+this.value+')').show();
  }
});

$('[name="equip"]').on('click',function(){
  $('[name="enchant"]').prop('checked',false);
  $('[name="position[]"]').prop('checked',false);
});

$('[name="enchant"]').on('click',function(){
  $('[name="equip"]').prop('checked',false);
});

$('#scroll_to_top').on('click',function(){
  UIkit.scroll($(this)).scrollTo('body');
});


$('.ajax').on('click',function(){
  $('#result').empty();
  $('#result').append('<div id="loading"><div>検索しています...</div><div uk-spinner="ratio: 3"></div></div>');
});

var position = [];
$('#position [type=checkbox]').on('change',function(){
  if ($(this).prop('checked')) {
    position.push($(this).val());
  } else {
    position.splice(position.indexOf($(this).val()),1);
  }
});

$('.ajax').on('click',function(){
  if ($('[type=radio]:checked').val() == undefined) {
    $('#result').empty();
    $('#result').append('<div class="notice">エンチャントを選択してください</div>');
  } else {

    if ($('[name="enchant"][type=radio]:checked').val() == undefined) {
      var enchant = $('[name="enchant"][type=radio]:checked').val();
    } else {
      var enchant = $('[name="enchant"][type=radio]:checked').val().replace(/ /g,'');
    }

    var equip = $('[name="equip"][type=radio]:checked').val();

    $.ajax({
      url:'search.php',
      datatype: 'json',
      data: {
        'enchant': enchant,
        'position': position,
        'equip': equip
      }
    })
    .then(
      function(data) {
        //console.log(data);
        $('#result').empty();
        var fragment = document.createDocumentFragment();
        var item = data;
        let resulthead = document.createElement('h2');
        if (enchant == undefined) {
          resulthead.textContent = '検索結果 : '+item[equip]['itemname'];
        } else {
          resulthead.textContent = '検索結果 : '+$('[type=radio]:checked').val();
        }

        fragment.appendChild(resulthead);
        let itemfound = document.createElement('div');
        if (!Object.keys(item).length) {
          itemfound.textContent = '該当するアイテムはありません';
        } else {
          itemfound.textContent = Object.keys(item).length+' 件 HITしました';
        }
        fragment.appendChild(itemfound);
        for (let itemid in item) {
          let $item = document.createElement('div');
          $item.id = itemid;
          $item.className = 'item uk-card uk-card-default uk-card-body uk-width-1-1@m uk-margin';
          let $itemname = document.createElement('h4'); //itemname
          $itemname.className = 'itemname uk-card-title';
          $itemname.textContent = item[itemid]['itemname']+'['+item[itemid]['slot']+']';

          let $encha_category = document.createElement('div');
          $encha_category.className = 'enchant_category';
          $encha_category.textContent = item[itemid]['enchant'];
          let $ul = document.createElement('ul');
          $ul.className = 'enchant_list uk-list';
          $item.appendChild($itemname);
          $item.appendChild($encha_category);

          for (var key in item[itemid]) {
            //装備位置はじまり
            if (key == 'category') {
              var $category_position = document.createElement('div');
              $category_position.className = 'equippment_category uk-label';
              if (item[itemid][key] == '兜') {
                var helm = true;
              } else {
                $category_position.textContent = item[itemid][key];
                var helm = false;
                $item.appendChild($category_position);
              }
            } else if (key == 'position') {
              if (helm == true && item[itemid][key] == '上段') {
                $category_position.textContent = '頭上段';
              } else if (helm == true && item[itemid][key] == '中段') {
                $category_position.textContent = '頭中段';
              } else if (helm == true && item[itemid][key] == '下段') {
                $category_position.textContent = '頭下段';
              }
              $item.appendChild($category_position);
              //装備位置終わり
            } else if (key != 'itemname' && key != 'slot' && key != 'enchant' && item[itemid][key] != null) {
              //第○エンチャント
              let $li = document.createElement('li');
              if (item[itemid][key]['found']) {
                $li.className = key + ' found';
              } else {
                $li.className = key + ' notfound';
              }
              $li.textContent = key;
              let $flex = document.createElement('div');
              $flex.className = 'uk-flex uk-flex-wrap';

              for (var enchaid in item[itemid][key]) {
                if (enchaid != 'found') {
                  let $enchaname = document.createElement('div');
                  if (item[itemid][key][enchaid] == $('[type=radio]:checked').val()) {
                    $enchaname.className = 'enchantname target';
                  } else {
                    $enchaname.className = 'enchantname';
                  }
                  $enchaname.textContent = item[itemid][key][enchaid];
                  $flex.appendChild($enchaname);
                }
              $li.appendChild($flex);
              }
            $ul.appendChild($li);
            }

          }
          $item.appendChild($ul);
          let $roma_link = document.createElement('a');
          $roma_link.href = 'https://ro.mukya.net/items/'+item[itemid]['itemname']+'/';
          $roma_link.target = '_blank';
          let str = document.createTextNode('RO-MAで見る');
          $roma_link.appendChild(str);
          let $roma = document.createElement('div');
          $roma.appendChild($roma_link);
          $item.appendChild($roma)
          fragment.appendChild($item);
        }
        $('#result').append(fragment);
        $('body').css('height','100%');
        UIkit.scroll($(this)).scrollTo('#result');
      },
      function() {
        alert('connection failed');
      }
    );
  }

});
