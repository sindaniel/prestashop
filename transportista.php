public function returnNewPayment($id_cart, $id_address_delivery, $totalPay){

        
        
        //obtengo el peso y valor del pedido
        $sqlProducts = '
            SELECT * FROM  `'._DB_PREFIX_.'cart_product`
            LEFT JOIN `'._DB_PREFIX_.'product` USING (id_product)
            WHERE id_cart ='.$id_cart;
        $products = Db::getInstance()->executeS($sqlProducts);
            
        $pesoTotal = 0;
        foreach ($products as $key => $product) {
            $pesoTotal = $pesoTotal + $product['weight'];
        }
       
        $direccion = Db::getInstance()->getRow('SELECT id_state, city FROM  `'._DB_PREFIX_.'address` WHERE id_address='.$id_address_delivery);
        $tipo = Db::getInstance()->getRow('SELECT tipo, price FROM  `'._DB_PREFIX_.'ciudades` WHERE id_dpto='.$direccion['id_state']." AND id_ciudad=".$direccion['city']);

        switch ($tipo['tipo']) {
            case 0:
            //nacional
                $tipo['tipo']  = 11;
                break;
            case 1:
            //regional
                $tipo['tipo']  = 10;
                break;
            case 2:
            //urbano
                $tipo['tipo']  = 9;
                break;   
            default:
                $tipo['tipo']  = 11;
                break;
        }


        //Seguro
        $seguro = 0;
        if($totalPay > 10000){
            $seguro = ceil($totalPay*0.01);
        }

        $carrierSql = '
            SELECT *  FROM `'._DB_PREFIX_.'delivery` D
            LEFT JOIN `'._DB_PREFIX_.'range_weight` W USING(id_range_weight) 
            WHERE 
            D.`id_carrier` =18 AND 
            D.`id_shop` = 1 AND
            D.`id_zone` = '.$tipo['tipo'].' AND
            W.`delimiter1` < '.$pesoTotal.' AND
            W.`delimiter2` >= '.$pesoTotal.' 
            ';
        $carrier = Db::getInstance()->getRow($carrierSql);
        
        if($pesoTotal <= 30){
            $totalEnvio = $seguro + $carrier['price'];
        }else{
            $totalEnvio = $pesoTotal*$tipo['price'];
        }
      $newData = array();
        $newData['totalEnvio'] = $totalEnvio;
        $newData['totalPedido'] = $totalEnvio + $totalPay;
        return $newData;
    }
