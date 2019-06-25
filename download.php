<?php

//执行脚本前先安装这两个依赖： sudo apt install php-xml php-mbstring

//这里以下载小说 ‘http://www.xx31xs.org/134/134314/’ 为例子，原理很简单就是先下载目录页，然后下载每个章节内容，最后保存为txt文件

$host = "http://www.xx31xs.org";
$toc = file_get_contents($host . '/14/14133/');

$toc = iconv('GBK', 'UTF-8', $toc);//把gbk编码转换为utf8
$tocDomDoc = new DOMDocument();
$toc = mb_convert_encoding($toc, "HTML-ENTITIES", "UTF-8");
$tocDomDoc->loadHTML($toc);

$novelName = $tocDomDoc->getElementsByTagName("title")->item(0)->nodeValue;
$novelContent = "";

//get all dd
$ddItems = $tocDomDoc->getElementsByTagName('dd');
//get all a
for ($i = 0; $i < $ddItems->length; $i++) {
    $aItem = $ddItems->item($i)->childNodes->item(0);
    if (!$aItem) {
        continue;
    }
    $chapterTitle = $aItem->nodeValue;

    if (!$aItem->attributes) {
        continue;
    }
    if (!$aItem->attributes->getNamedItem("href")) {
        continue;
    }
    if (!$aItem->attributes->getNamedItem("href")->nodeValue) {
        continue;
    }
    $chapterUrl = $host . $aItem->attributes->getNamedItem("href")->nodeValue;

    echo $chapterTitle . ">" . $chapterUrl . "\n";

    $chapterHtml = file_get_contents($chapterUrl);
    $chapterHtml = iconv('GBK', 'UTF-8', $chapterHtml);
    $chapterDomDoc = new DOMDocument();
    $chapterHtml = mb_convert_encoding($chapterHtml, "HTML-ENTITIES", "UTF-8");
    $chapterDomDoc->loadHTML($chapterHtml);

    $textContent = $chapterDomDoc->textContent;

    $pos1 = mb_strpos($textContent, "contentup");
    $pos2 = mb_strpos($textContent, "bdshare");
    $textContent = mb_substr($textContent, $pos1 + 13, $pos2 - $pos1 - 13);

    $novelContent .= $chapterTitle ."\r\n\r\n". $textContent;
}

file_put_contents($novelName.".txt", $novelContent);
