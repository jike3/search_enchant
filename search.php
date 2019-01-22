<?php
  $enchantdict = array();

  $search = filter_input(INPUT_GET, 'enchant'); //エンチャント名
  $equip = filter_input(INPUT_GET, 'equip'); //装備名

  if (!empty($_GET['position'])) {
    if (is_string($_GET['position'])) {
      $error = '装備箇所が不正です';
    } else {
      $pos = $_GET['position']; //装備位置
    }

    $positions = [];
    foreach ($pos as $v) {
      if ($v == '頭上段') {
        $positions[] = 'helms.category = "兜" and helms.position = "上段"';
      } elseif ($v == '頭中段') {
        $positions[] = 'helms.category = "兜" and helms.position = "中段"';
      } elseif ($v == '頭下段') {
        $positions[] = 'helms.category = "兜" and helms.position = "下段"';
      } elseif ($v == '武器') {
        $positions[] = 'weapons.category LIKE "%剣%" or weapons.category = "カタール" or weapons.category LIKE "%斧%" or weapons.category LIKE "%槍%" or weapons.category = "鈍器" or weapons.category LIKE "%杖%" or weapons.category = "爪" or weapons.category = "楽器" or weapons.category = "鞭" or weapons.category = "弓" or weapons.category = "本" or weapons.category LIKE "%手裏剣%" or weapons.category = "ライフル" or weapons.category LIKE "%ガン"';
      } elseif ($v == 'アクセサリー') {
        $positions[] = 'armor.category LIKE "アクセサリー%"';
      } else {
      $positions[] = 'armor.category = "'.$v.'"';
      }
    }
  }

  require '/usr/home/jike/conf/connection.php';

  try {

    $pdo = new PDO(DSN, USER, PASSWORD,[PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    $columnname = array('1st'=>'第1エンチャント', '2nd'=>'第2エンチャント', '3rd'=>'第3エンチャント', '4th'=>'第4エンチャント' );
    //エンチャント名からcontainidを検索
    $stmt = $pdo->prepare('SELECT con.contain_id, en.enchant_id, en.enchant_name
                           FROM rodb_enchants AS en
                           INNER JOIN rodb_contain_to_enchants AS con
                           ON con.enchant_id = en.enchant_id
                           WHERE enchant_name = ?');
    $stmt->bindValue(1,$search,PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll();

    $query = "
    SELECT itemname.itemid, itemname.itemname, slot.slot, categorytable.category AS category, positiontable.position AS position, en.enchant_category AS enchant,
                                             en.1st_enchants AS `第1エンチャント`, en.2nd_enchants AS `第2エンチャント`, en.3rd_enchants AS `第3エンチャント`, en.4th_enchants AS `第4エンチャント`
                                             FROM rodb_can_enchant_items AS en
                                             INNER JOIN rodb_itemname AS itemname
                                             ON en.itemid = itemname.itemid
                                             LEFT JOIN rodb_armors AS armor
                                             ON armor.itemid = en.itemid
                                             LEFT JOIN rodb_armor_helms AS helms
                                             ON helms.itemid = en.itemid
                                             LEFT JOIN rodb_weapons AS weapons
                                             ON weapons.itemid = en.itemid
                                             LEFT JOIN rodb_slot AS slot
                                             ON en.itemid = slot.itemid
                                             LEFT JOIN (
                                               SELECT rodb_can_enchant_items.itemid, rodb_armors.category
                                               FROM rodb_can_enchant_items
                                               INNER JOIN rodb_armors ON rodb_can_enchant_items.itemid = rodb_armors.itemid
                                               UNION ALL
                                               SELECT rodb_can_enchant_items.itemid, rodb_weapons.category
                                               FROM rodb_can_enchant_items
                                               INNER JOIN rodb_weapons ON rodb_can_enchant_items.itemid = rodb_weapons.itemid
                                               UNION ALL
                                               SELECT rodb_can_enchant_items.itemid, rodb_armor_helms.category
                                               FROM rodb_can_enchant_items
                                               INNER JOIN rodb_armor_helms ON rodb_can_enchant_items.itemid = rodb_armor_helms.itemid
                                             ) AS categorytable
                                             ON en.itemid = categorytable.itemid
                                             LEFT JOIN (
                                               SELECT rodb_can_enchant_items.itemid, rodb_armors.position
                                               FROM rodb_can_enchant_items
                                               INNER JOIN rodb_armors ON rodb_can_enchant_items.itemid = rodb_armors.itemid
                                               UNION ALL
                                               SELECT rodb_can_enchant_items.itemid, rodb_armor_helms.position
                                               FROM rodb_can_enchant_items
                                               INNER JOIN rodb_armor_helms ON rodb_can_enchant_items.itemid = rodb_armor_helms.itemid
                                             ) AS positiontable
                                             ON en.itemid = positiontable.itemid";

    //containidを含むアイテムを検索
    $item = array();
    if (empty($positions)) { //装備箇所指定なし

      if (!empty($equip)) { //装備から検索
        $search_equip = $pdo->prepare($query.' WHERE en.itemid = ?');
        $search_equip->bindValue(1,$equip,PDO::PARAM_INT);
        $search_equip->execute();
        $search_equip->fetchAll(PDO::FETCH_FUNC, "search_result");
      } else {
        $search_canenchant_item = $pdo->prepare($query.' WHERE en.1st_enchants = ? or en.2nd_enchants = ? or en.3rd_enchants = ? or en.4th_enchants = ?');
        foreach ($result as $num => $itemdata) {
          for ($i=1; $i < 5; $i++) {
            $search_canenchant_item->bindValue($i,$itemdata['contain_id'],PDO::PARAM_STR);
          }
          $search_canenchant_item->execute();
          $search_canenchant_item->fetchAll(PDO::FETCH_FUNC, "search_result");
        }
        $search_canenchant_item = bind_enchant_position($result, $search_canenchant_item);
      }

    } else { //装備箇所指定あり
      $search_canenchant_item = $pdo->prepare($query.' WHERE (en.1st_enchants = ? or en.2nd_enchants = ? or en.3rd_enchants = ? or en.4th_enchants = ?) and ('.implode(' or ',$positions).');');
      foreach ($result as $num => $itemdata) {
        for ($i=1; $i < 5; $i++) {
          $search_canenchant_item->bindValue($i,$itemdata['contain_id'],PDO::PARAM_STR);
        }
        $search_canenchant_item->execute();
        $search_canenchant_item->fetchAll(PDO::FETCH_FUNC, "search_result");
      }
      $search_canenchant_item = bind_enchant_position($result, $search_canenchant_item);
    }



    //$item = { ナンバー => {
    //                       キー：アイテムデータ => 値：アイテムデータ
    //                       }
    //         }

    // {エンチャントID：エンチャント名} をenchantdictに格納
    $enchantdata = $pdo->prepare('SELECT con.contain_id, en.enchant_id, itemname.itemname AS enchant_name FROM rodb_contain_to_enchants AS con INNER JOIN rodb_enchants AS en ON con.enchant_id = en.enchant_id INNER JOIN rodb_itemname AS itemname ON en.enchant_id = itemname.itemid;');
    $enchantdata->execute();
    $enchant_res = $enchantdata->fetchAll(PDO::FETCH_FUNC, "enchantdict");

    //enchantdictからIDを照合してエンチャント名を代入する
    foreach ($item as $num => &$itemdata) {
      foreach ($itemdata as $ek => &$ev) {
        //第○エンチャント以外はなにもしない
        if ($ek === 'itemname') {
        } elseif ($ek === 'enchant') {
        } elseif ($ek === 'itemid') {
        } elseif ($ek === 'slot') {
        } elseif ($ek === 'category') {
        } elseif ($ek === 'position') {
        } else {
          if ($ev != null) {
            $ev = $enchantdict[$ev];
          }
          if (($ev != null && in_array($search,$ev)) || ($ev != null && !empty($equip))) { //検索されたエンチャント名があった場合はfoundをtrueにする（第1にあっても第2第3にあるとは限らないため）
            $itemdata[$ek]['found'] = true;
          }
        }
      }
      unset($ev);
    }
    unset($itemdata);

    //json形式で出力
    header('Content-type: application/json');
    echo json_encode($item,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

      } catch (PDOException $e) {
    exit('FAILED'. $e->getMessage());
  }

  function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }

  function enchantdict($contain, $id, $name){
    global $enchantdict;
    $enchantdict[$contain][$id] = $name;
  }

  //id=アイテムID name=アイテム名 category=装備種類 position＝装備位置 enchant=エンチャントの種類 1,2,3,4=第○エンチャント
  function search_result($id, $name, $slot, $category, $position, $enchant, $first, $second, $third, $fourth){
    global $item;
    if (is_null($slot)) {
      $item[$id] = array('itemname' => $name, 'slot' => 0, 'category' => $category, 'position' => $position, 'enchant' => $enchant, '第1エンチャント' => $first, '第2エンチャント' => $second, '第3エンチャント' => $third, '第4エンチャント' => $fourth);
    } else {
      $item[$id] = array('itemname' => $name, 'slot' => $slot, 'category' => $category, 'position' => $position, 'enchant' => $enchant, '第1エンチャント' => $first, '第2エンチャント' => $second, '第3エンチャント' => $third, '第4エンチャント' => $fourth);
    }
  }

  function bind_enchant_position($result, $fetchedarray){

  }
?>
