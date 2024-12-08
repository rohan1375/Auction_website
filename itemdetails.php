<?php
global $session;

use App\Exceptions\ClassException;
use App\Lib\Logger;
use App\Models\Bid;
use App\Models\Item;
use App\Models\User;

require_once (__DIR__. '/../app/bootstrap.php');

$validid = pf_validate_number($_GET['id'], "redirect", CONFIG_URL);

try {
    $item = Item::findFirst(["id" => "$validid"]);
}catch (ClassException $e){
    Logger::getLogger()->critical("Invalid item: ",['exception'=>$e]);
    echo "Invalid Item";
    die();
}

if (isset($_POST['submit'])) {
    if (is_numeric($_POST['bid']) == false){
    header("Location: itemdetails.php?id=".$validid . "&error=letter");
    die();
    }
}
$validbid = false;
if (count($item->get('bindObjs')) == 0){
    $price = intval($item->get('price'));
    $postedBid = intval($_POST['bid']);

    if ($postedBid >= $price){
        $validbid = true;
    }
}else{
    $bids = $item->get('bindObjs');
    $highestBid = array_shift($bids);
    $highestBid = intval($highestBid -> get ('amount'));
    $postedBid = intval($_POST['bid']);
    if ($postedBid >= $highestBid){
        $validbid = true;
    }
}

if ($validbid == false){
    header("Location: itemdetails.php?id=".$validid . "&error=lowprice#bidbox");
    die();
}else{
    $newBid = new Bid($item->get('id'), $_POST['bid'], $session->getUser()->get('id'));
    $newBid->create();
    header("Location: itemdetails.php?id=".$validid);
    die();
}
$nowepoch = time();
$itempoch = strtotime($item->get('date'));

$validAuction = false;
if ($itempoch > $nowepoch) {
    $validAuction = true;
}
