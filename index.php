<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <title></title>
  </head>
  <body>

    <?php
      set_time_limit(180);

      include_once 'simplehtmldom\HtmlWeb.php';
      use simplehtmldom\HtmlWeb;
      $client = new HtmlWeb();

      $linkAll = [
        // 'https://www.electronictoolbox.com/category/83118/new-products/',  // Слишком долго грузится 4700 страниц
        // 'https://www.electronictoolbox.com/category/53388/upg-sealed-lead-acid/',
        // 'https://www.electronictoolbox.com/category/53389/upg-security-solutions/',
        // 'https://www.electronictoolbox.com/category/53390/upg-adventure-power/',
        'https://www.electronictoolbox.com/category/53391/upg-adventure-power-marine/',
        'https://www.electronictoolbox.com/category/53392/upg-flame-retardant-sealed-lead-acid/'
      ];

      $fileProducts = fopen('fileProducts.json', 'w');
      foreach ($linkAll as $keylinkCat => $linkCat) {

        $pruductLoad = $client->load($linkCat);
        $pruductCatOut = $pruductLoad->find('h1[class=title fw-bold]',0);
        $pruductCatOutToFile = trim($pruductCatOut->innertext);
        $pruductCatOutToFileHtml = '<hr><h3>'.$pruductCatOutToFile.'</h3><hr>';

        $productsInformToFile[] = $pruductCatOutToFileHtml;

         $numPage = 1;
         do {
              $headers = array(
                  'X-Requested-With: XMLHttpRequest'
              );

                $options = array(
                    CURLOPT_URL => $linkCat.'?page='.$numPage,

                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_CONNECTTIMEOUT => 20,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36' //'your_user_agent'
                );
                $curl = curl_init();
                curl_setopt_array($curl, $options);
                $data = curl_exec($curl);
                curl_close($curl);
                $dataJson = json_decode($data);

                $numPagesEnd = $dataJson->pager->total;
                $numPagesAll = intdiv($numPagesEnd,20)+1;

                $is=0;
                foreach ($dataJson->items as $obj) {
                  $pruductPageLink = $client->load('https://www.electronictoolbox.com'.$obj->url);
                  $pruductImage = $pruductPageLink->find('img',0);
                  $descriptOut = $pruductPageLink->find('article[class=description-product-content],br',0);
                  $productcodeOut = $pruductPageLink->find('div[class=value],span,a',0);
                  $priceOut = $pruductPageLink->find('span[class=price]',0);
                  $priceDisOut = $pruductPageLink->find('span[class=price]',1);

                  $productcodeOutToFile = trim($productcodeOut->innertext);
                  $nameToFile = $obj->name;
                  if($obj->description) {
                    $shortDescriptionToFile = $obj->description;
                  }
                  if ($obj->brand) {
                    $brandToFile = trim($obj->brand);
                  }
                  if($pruductImage->src) {
                    $pruductImageToFile = $pruductImage->src;
                  }
                  $priceOutToFile = $priceOut->innertext;
                  if($priceDisOut) {
                    $priceDisOutToFile = $priceDisOut->innertext;
                  }
                  $descriptOutToFile = trim($descriptOut->innertext);
                  $productsInformToFile[] = $productcodeOutToFile.'<br>'.$nameToFile.'<br>'.$shortDescriptionToFile.'<br>'.$brandToFile.'<br>'.$pruductImageToFile.'<br>'.$priceOutToFile.'<br>'.$priceDisOutToFile.'<br>'.$descriptOutToFile.'<hr>';

                  $linksOfImagesArr[$is] = $pruductImage->src;
                  $is++;
                }
            $numPage++;
         } while ($numPage <= $numPagesAll);
      }
         //Вывести в браузере
      foreach ($productsInformToFile as $print) {
        echo $print;
      }
      $productsInformToFileEncode = json_encode($productsInformToFile);
      $linksOfImagesArr = array_unique($linksOfImagesArr);

      $linksOfImagesEncode = json_encode($linksOfImagesArr);
      $fullListToFile = $productsInformToFileEncode.$linksOfImagesEncode;
      fwrite($fileProducts,$fullListToFile);
      fclose($fileProducts);
     ?>

  </body>
</html>
