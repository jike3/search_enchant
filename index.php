<?php
  require '/usr/home/jike/conf/connection.php';
  try {

    $pdo = new PDO(DSN, USER, PASSWORD,[PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);

    $stmt = $pdo->query('SELECT * FROM rodb_enchants');
    $result = $stmt->fetchAll();
    $normal = $pdo->query('SELECT rodb_enchants.enchant_id, rodb_itemname.itemname AS enchant_name, rodb_enchants.reading, rodb_enchants.effect FROM rodb_enchants INNER JOIN rodb_itemname ON rodb_enchants.enchant_id = rodb_itemname.itemid WHERE special = "0"')->fetchAll();
    $special = $pdo->query('SELECT rodb_enchants.enchant_id, rodb_itemname.itemname AS enchant_name, rodb_enchants.reading, rodb_enchants.effect FROM rodb_enchants INNER JOIN rodb_itemname ON rodb_enchants.enchant_id = rodb_itemname.itemid WHERE special = "1"')->fetchAll();
    $equip = $pdo->query('SELECT en.itemid, name.itemname, name.reading, slot.slot FROM rodb_can_enchant_items AS en INNER JOIN rodb_itemname AS name ON en.itemid = name.itemid LEFT JOIN rodb_slot AS slot ON en.itemid = slot.itemid')->fetchAll();
  } catch (PDOException $e) {
    exit('FAILED'. $e->getMessage());
  }
  $equip_position = ['頭上段','頭中段','頭下段','鎧','武器','盾','肩にかける物','靴','アクセサリー'];
  function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-51164693-4"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-51164693-4');
    </script>
    <meta charset="utf-8">
    <title>ROエンチャント逆引き</title>
    <meta name="description" content="ラグナロクオンラインのエンチャント名（鋭利、闘志など）からそれをエンチャントできるアイテムを検索します。">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.25/css/uikit.min.css" />
    <link rel="stylesheet" href="css/style.css?201901221953" />
    <link rel="stylesheet" media="(max-width: 480px)" href="css/style-480.css?201901150047">
    <!-- UIkit JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.25/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.25/js/uikit-icons.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  </head>
  <body>
    <header class="uk-container uk-container-small">
      <h1 class="uk-heading-divider">ROエンチャント逆引き</h1>
      <div class="description">
        鋭利、闘志のようなエンチャント名からそれをエンチャントできるアイテムを検索します。<br>
        エンチャント名にマウスオーバーすると効果が表示されます。
      </div>
    </header>
    <main id="container" class="uk-container uk-container-small">

      <div class="enchant">
        <div id="search" class="uk-search uk-search-navbar">
          <span uk-search-icon></span>
          <input id="searchbox" class="uk-search-input" type="search" placeholder="エンチャント名を入力">
        </div>

          <div id="incremental_result">
            <div class="uk-flex uk-flex-wrap">
              <?php foreach ($normal as $Nencha): ?>
                <label<?php if ($Nencha['effect'] != NULL):?><?=' title="'.h($Nencha['effect']).'"'?><?php endif; ?>>
                  <span style="display:none;"><?=h($Nencha['reading'])?></span>
                  <input class="uk-radio <?=h($Nencha['enchant_id'])?>" type="radio" name="enchant" value="<?=h($Nencha['enchant_name'])?>"> <?=h($Nencha['enchant_name'])?>
                </label>
              <?php endforeach; ?>
              <?php foreach ($special as $Sencha): ?>
                <label<?php if ($Sencha['effect'] != NULL):?><?=' title="'.h($Sencha['effect']).'"'?><?php endif; ?>>
                  <span style="display:none;"><?=h($Sencha['reading'])?></span>
                  <input class="uk-radio <?=h($Sencha['enchant_id'])?>" type="radio" name="enchant" value="<?=h($Sencha['enchant_name'])?>"> <?=h($Sencha['enchant_name'])?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div id="allofenchants" uk-accordion>
            <div>
              <a class="uk-accordion-title uk-text-small" href="#">すべてのエンチャントから選ぶ</a>
              <div class="uk-accordion-content">

                <div id="NormalEnchant" class="enchadata">
                  <div class="catehead uk-heading-bullet">通常エンチャント</div>
                  <div class="uk-flex uk-flex-wrap" id="Nencha">
                    <?php foreach ($normal as $Nencha): ?>
                      <label<?php if ($Nencha['effect'] != NULL):?><?=' title="'.h($Nencha['effect']).'"'?><?php endif; ?>>
                        <span style="display:none;"><?=h($Nencha['reading'])?></span>
                        <input class="uk-radio i<?=h($Nencha['enchant_id'])?>" type="radio" name="enchant" value="<?=h($Nencha['enchant_name'])?>"> <?=h($Nencha['enchant_name'])?>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div id="SpecialEnchant" class="enchadata">
                  <div class="catehead uk-heading-bullet">特殊エンチャント</div>
                  <div class="uk-flex uk-flex-wrap" id="Sencha">
                    <?php foreach ($special as $Sencha): ?>
                      <label<?php if ($Sencha['effect'] != NULL):?><?=' title="'.h($Sencha['effect']).'"'?><?php endif; ?>>
                        <span style="display:none;"><?=h($Sencha['reading'])?></span>
                        <input class="uk-radio i<?=h($Sencha['enchant_id'])?>" type="radio" name="enchant" value="<?=h($Sencha['enchant_name'])?>"> <?=h($Sencha['enchant_name'])?>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div id="option" uk-accordion>
            <div>
              <a class="uk-accordion-title uk-text-small" href="#">オプション</a>
              <div class="uk-accordion-content">
                <div class="catehead uk-heading-bullet">部位</div>
                <div id="position" class="uk-flex uk-flex-wrap uk-margin-small-left">
                  <?php foreach ($equip_position as $position): ?>
                    <label><input class="uk-checkbox" type="checkbox" name="position[]" value="<?=h($position)?>"> <?=h($position)?></label>
                  <?php endforeach; ?>
                </div>
                <!--<div>装備可能職業</div>
                <div id="equippable_jobs" class="uk-flex uk-flex-wrap">
                  <label><input class="uk-checkbox" type="checkbox"> AB</label>
                </div>-->
              </div>
            </div>
          </div>
          <div id="allofequip" uk-accordion>
            <ul uk-accordion="multiple: true">
              <div>
                <a class="uk-accordion-title uk-text-small" href="#">すべての装備から選ぶ</a>
                <div class="uk-accordion-content">
                  <div id="equip_search" class="uk-search uk-search-default uk-margin-left">
                    <span uk-search-icon></span>
                    <input id="equip_searchbox" class="uk-search-input" type="search" placeholder="装備名を入力">
                  </div>
                  <div id="equip_result" class="uk-flex uk-flex-wrap">
                  <?php foreach ($equip as $equipdata): ?>
                    <label style="display:none;">
                      <span style="display:none;"><?=h($equipdata['reading'])?></span>
                      <?php if($equipdata['slot'] == NULL): ?>
                      <input class="uk-radio <?=h($equipdata['itemid'])?>" type="radio" name="equip" value="<?=h($equipdata['itemid'])?>"> <?=h($equipdata['itemname']).' [0]'?>
                      <?php else: ?>
                      <input class="uk-radio <?=h($equipdata['itemid'])?>" type="radio" name="equip" value="<?=h($equipdata['itemid'])?>"> <?=h($equipdata['itemname']).' ['.h($equipdata['slot']).']'?>
                      <?php endif; ?>
                    </label>
                  <?php endforeach; ?>
                  </div>
                </div>
              </div>
          </div>
          <div class="searchbutton">
            <button class="ajax uk-button uk-button-primary uk-button-large" type="button">検索</button>
          </div>
      <hr>
        <div id="result">

        </div>
      </div>
      <a id="scroll_to_top" class="uk-button uk-button-primary" uk-icon="icon: chevron-up"></a>
    </main>

    <footer>
      <div>
        <p>
          ROエンチャント逆引き by <a href="https://ro-mastodon.puyo.jp/@jike">JIKE</a><br>
          (c)Gravity Co., Ltd. &amp; Lee MyoungJin(studio DTDS). All rights reserved.<br>
          (c)GungHo Online Entertainment, Inc. All Rights Reserved.
        </p>
      </div>
    </footer>

    <script src="main.js?201905262312"></script>
  </body>
</html>
