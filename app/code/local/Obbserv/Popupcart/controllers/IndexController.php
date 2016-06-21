<?php 

class Obbserv_Popupcart_IndexController extends Mage_Core_Controller_Front_Action{

	public function indexAction()
	{
	    $this->loadLayout();
    	    $this->renderLayout();
	}

	public function postAction(){
		$pid = $_POST['id']; 
		
		$_product= Mage::getModel('catalog/product')->load($pid);
		$_childData = array();
		
		$unit = '';	
		$type="configurable";
		
		if($_product->isConfigurable()){
			
			$childProducts = Mage::getModel('catalog/product_type_configurable')
				    ->getChildrenIds($pid);
	
			foreach($childProducts as $_Ids){
				foreach($_Ids as $_Id){
					$childdata = Mage::getModel('catalog/product')->load($_Id);				
					$_childData[] = array(
						"id"=>$childdata->getId(),
						"units"=>$childdata->getAttributeText('units'),
						"price"=>Mage::helper('core')->currency(trim($childdata->getPrice()), true, false)
					);
				}
			}
			
			$unit = $childdata->getAttributeText('units');

		}else{
			$unit = $_product->getAttributeText('units');
			$type="simple";
		}

		$_productData = array(
				"id"=>$_product->getId(),
				"name"=>$_product->getName(),
				"short_description"=>$_product->getShortDescription(),
				"description"=>$_product->getDescription(),
				"price"=>Mage::helper('core')->currency(trim($_product->getPrice()), true, false),
				"special_price"=>Mage::helper('core')->currency(trim($_product->getSpecialPrice()), true, false),
				"product_url"=>$_product->getProductUrl(),
				"image"=>$_product->getImageUrl(),
				"units"=>$unit,
				"children"=>$_childData,
				"mytype"=>$type
				);
		
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_productData));
		//print_r($_productData);
	}

	public function childAction(){

		$pid = $_POST['id']; 
		
		$_product= Mage::getModel('catalog/product')->load($pid);

		$_productData = array(
				"id"=>$_product->getId(),
				"name"=>$_product->getName(),
				"short_description"=>$_product->getShortDescription(),
				"description"=>$_product->getDescription(),
				"price"=>Mage::helper('core')->currency(trim($_product->getPrice()), true, false),
				"special_price"=>Mage::helper('core')->currency(trim($_product->getSpecialPrice()), true, false)
				);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_productData));
	}

	public function ajaxcartAction(){

		if(isset($_POST['id']) && isset($_POST['qty'])){
			$id = $_POST['id']; 
			$qty = $_POST['qty'];
			$cart = Mage::getModel('checkout/cart');
		    $cart->init();
			$val = $this->checkItem($id);
			$html_output = "<br/>";
			$product = Mage::getModel('catalog/product')->load($id);
			if($val==false){
			    $message = $this->__("The requested quantity for '%s' is not available.", Mage::helper('core')->escapeHtml($product->getName()));
			    Mage::getSingleton('core/session')->addError($message);
			    print 1;
			}elseif($val==true){
				try{
					$productModel = Mage::getModel('catalog/product')->load($id);
					$cart->addProduct($productModel, array('qty' => $qty));  
					$cart->save();
				}catch(Exception $e){
					$message = $this->__('Please specify the product required option(s).');
                    Mage::getSingleton('core/session')->addError($message);
					print 3;
				}
			}else{
				print 2;
			}
		}
 	}

    /*
     * Ajax add to cart Popup Shopping cart 
     */
    public function popupajaxcartAction(){

		if(isset($_POST['id']) && isset($_POST['qty'])){
			$id = $_POST['id']; 
			$qty = $_POST['qty'];
			$cart = Mage::getModel('checkout/cart');
		    $cart->init();
			$val = $this->checkItem($id);
			$html_output = "<br/>";
			$product = Mage::getModel('catalog/product')->load($id);
			if($val==false){
			        print 1;
			}elseif($val==true)
			{
				try{
					$productModel = Mage::getModel('catalog/product')->load($id);
					$cart->addProduct($productModel, array('qty' => $qty));  
					$cart->save();
				}catch(Exception $e){
					print 3;
				}
			}else{
				print 2;
			}
		}
 	}

	public function rendersidebarAction() {
            echo Mage::helper('checkout/cart')->getItemsCount();
            echo '@@@WW@@**WWW*WWW**@@#@#@@**WWW*WWW**@@WW@@@';
            
            print $this->getLayout()->createBlock('checkout/cart_sidebar')
                    ->setTemplate('checkout/cart/sidebar.phtml')->toHtml();
	}
        
    public function productviewAction() {
        print $this->getLayout()->createBlock('catalog/product_view')
            ->setProductId($_POST['id'])
            ->setTemplate('catalog/slider/quick_view.phtml')->toHtml();
    }


	private function checkitem($id) {
		$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems(); 
		$ret = $val = false;
		$model = Mage::getModel('catalog/product')->load($id); 
		$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($model)->getQty();
		$cartQty = 0;
		if(count($items)>0){
			foreach($items as $item) {
				if($item->getProductId() == $id){
					$cartQty = $item->getQty();
					$ret = true;
					break;
				}
			}
		}else $ret = true;
		if($ret){
			if(($stocklevel-$cartQty)>0) return $val = true;
			elseif(($stocklevel-$cartQty)<=0) return $val = false;
		}else return $val = true;
		
	}
        
        public function rendersidebarwithqtyAction() {
            $qty = 0;
            if(!empty($_POST['id'])) {
                $id = $_POST['id']; 
                $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                foreach($items as $item) {
                    if($item->getProductId() == $id) {
                        $qty = $item->getQty();
                    }
                }
            }            
            echo $qty.'@@@W@@**WW*WW**@@#@#@@**WW*WW**@@W@@@';
            $this->rendersidebarAction();
        }

	public function removeitemAction(){
		if(isset($_POST['id'])){
			$cartHelper = Mage::helper('checkout/cart');
			$items = $cartHelper->getCart()->getItems();
			foreach($items as $item){
				if($item->getProduct()->getId() == $_POST['id']){
					$itemId = $item->getItemId();
					$cartHelper->getCart()->removeItem($itemId)->save();
					print 0;
					break;
				}
			}
		}
	}

	public function removesingleAction(){
		if(isset($_POST['id'])){
			$cartHelper = Mage::helper('checkout/cart');
			$items = $cartHelper->getCart()->getItems();
			foreach ($items as $item) {
			    if ($item->getProduct()->getId() == $_POST['id']) {
				if( $item->getQty() == 1 ){
				    $cartHelper->getCart()->removeItem($item->getItemId())->save();
				}
				else if($item->getQty() > 1){
				    $item->setQty($item->getQty() - 1);
				    $cartHelper->getCart()->save();
				    print 0;
				}
				break;
			    }
			}
		}
	}
    public function ajaxcartOptionsAction(){

		if(isset($_POST['id']) && isset($_POST['qty'])){

			$id = $_POST['id']; 
			$qty = $_POST['qty'];
            $options = $_POST['options'];
			$cart = Mage::getModel('checkout/cart');
		    $cart->init();

			$val = $this->checkItem($id);

			$html_output = "<br/>";

			if($val===false){
				print 1;
			}elseif($val===true)
			{
				try{
					$productModel = Mage::getModel('catalog/product')->load($id);
					$cart->addProduct($productModel, array('qty' => $qty, 'options' => array('2' => $options)));  
					$cart->save();
					print 0;
				}catch(Exception $e){
					print 3;
				}
			}else{
				print 2;
			}
		}
 	}

 	public function getSummaryCountAction() {
 		$quote = Mage::helper('checkout/cart')->getCart()->getQuote();
  	    echo round($quote->getItemsQty());
  	}

  	public function buyNowAction() {
        $request = Mage::app()->getRequest();
        /*store all serialize form data to variable $data */
        $data = $request->getParams();
        $id = $data['product'];
        $qty = $data['qty'];
        $product = Mage::getModel('catalog/product')->load($id);
        foreach ($data['options'] as $key => $value) {
        	$optionId = $key;
        	$optionValue = $value;
        }
        $cart = Mage::getModel('checkout/cart');
		$cart->init();
		$val = $this->checkItem($id);		
	    $productModel = Mage::getModel('catalog/product')->load($id);
		if($val===false){
			$message = $this->__("The requested quantity for '%s' is not available.", Mage::helper('core')->escapeHtml($product->getName()));
			Mage::getSingleton('core/session')->addError($message);
			echo 'notredirect';
		} else {
			try{
				$cart->addProduct($productModel, array('qty' => $qty, 'options' => array($optionId => $optionValue)));  
				if($cart->save() && $val==true){					
					$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
					Mage::getSingleton('core/session')->addSuccess($message);
					echo 'redirect';
	            }		
			} catch(Exception $e) {
				$message = $this->__("The requested quantity for '%s' is not available.", Mage::helper('core')->escapeHtml($product->getName()));
				Mage::getSingleton('core/session')->addError($message);
				echo 'notredirect';
			}
		} 	
    }

    public function popBuyNowAction() {
        $request = Mage::app()->getRequest();
        /*store all serialize form data to variable $data */
        $data = $request->getParams();
        $id = $data['product'];
        $qty = $data['qty'];
        $product = Mage::getModel('catalog/product')->load($id);
        foreach ($data['options'] as $key => $value) {
        	$optionId = $key;
        	$optionValue = $value;
        }
        $cart = Mage::getModel('checkout/cart');
		$cart->init();
		$val = $this->checkItem($id);		
	    $productModel = Mage::getModel('catalog/product')->load($id);
        if($val==false){
            print 1;
        } elseif($val==true) {
            try{
                $productModel = Mage::getModel('catalog/product')->load($id);
                $cart->addProduct($productModel, array('qty' => $qty, 'options' => array($optionId => $optionValue)));
                $cart->save();
            }catch(Exception $e){
                print 3;
            }
        } else {
            print 2;
        }	
    }
}

?>
