# HOME CREDIT - PHP CLASS pro API

__php class pro komunikaci s API - pro odeslání žádosti__

Dokumentace: <https://csoneclicknew.docs.apiary.io>

Script je zapouzdřením zdrojového kódu <https://github.com/homecreditcz/php-script> poskytnutý <https://github.com/homecreditcz>

## Init

    $_HOMECREDIT = new HomeCredit();
    
Default nastavení je pro test mód.

### Produkce
 
    $_HOMECREDIT->auth($shop, $username, $password);

## Získání tokenu

    if (!$_HOMECREDIT->setToken())
    {
      //  nepodařilo se získat token pro další komunikaci.            
    }

## Získání odkazu do Home Credit API

`(array)$HC_DATA` obsahuje asociativní pole parametrů nutných pro odeslání.

Result je uložený v `(array)$_HOMECREDIT->RESULT` odkud se dá číst erroring v případě neúspěchu.
    
Vzor: <https://github.com/homecreditcz/php-script/blob/master/json.php>

    if (!$_HOMECREDIT->createApplication($HC_DATA))
    {
      // nepodařilo se získat získat odkaz                        
    }
    else
    {
      $href = $_HOMECREDIT->getLink();
    }
                
## Debugging

Všechna CURL volání a jejich response se ukládají do stacku:

    (array)$_HOMECREDIT->debug
    
    
    
