<?php class ChatW extends CInputWidget
{
	private $baseUrl;
                    
    public $id='chat';
    public $model;
	public $ajaxUrl;
  
    public function run()
    {
	    $dir = dirname(__FILE__);
        $this->baseUrl = Yii::app()->getAssetManager()->publish($dir);
    	$cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile($this->baseUrl . '/assets/jquery-ui-1.10.3.custom.js');
        $cs->registerScriptFile($this->baseUrl . '/assets/jquery.ui.chatbox.js');
        $cs->registerCssFile($this->baseUrl . '/assets/jquery.ui.chatbox.css');
		echo '<input type="button"
    name="toggle" value="toggle" />
    <ul id="'.$this->id.'">
    </ul>
    
    ';
        
$script='
	  $(document).ready(function(){
          var box = null;
          $("input[type=\'button\']").click(function(event, ui) {
              if(box) {
                  box.chatbox("option", "boxManager").toggleBox();
              }
              else {
                  box = $("#'.$this->id.'").chatbox({
				  								id:"'.$this->id.'", 
                                                title : "test chat",
                                              	messageSent : function(id, user, msg) {
													$.post("'.$this->ajaxUrl.'",{"operation" : "saveMsg", "msg" : msg,"model" : "'.$this->model.'"}, 
										                function (data) {
															$("#"+id).chatbox("option", "boxManager").addMsg(data.user, data.msg, data.date);
															limitMsg();
										                },"json"
										            );
                                                }});
				getMessage();								
              }
          });
		  
      });
   function getMessage(){
   	$.post("'.$this->ajaxUrl.'",{"operation" : "getMsg","model" : "'.$this->model.'"}, 
                function (data) {
					$.each(data, function(i, item) {
						$("#'.$this->id.'").chatbox("option", "boxManager").addMsg(item.user_name, item.message, item.created);						
					});
					
                },"json"
            );
   	
   }
   function limitMsg(){
   	if($("#'.$this->id.' li").size()>15){
		$("#'.$this->id.' li:first").remove();
	}
   }
		';
   $cs->registerScript('cahat', $script);
    }
	
	static function Ajax()
    {
        $method = $_REQUEST['operation'];
		header("HTTP/1.0 200 OK");
        header('Content-type: text/json; charset=utf-8');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Pragma: no-cache");
        self::$method($_POST);
        
    }
	
	static function saveMsg($params)
    {
        $model = new $params['model'];
		$userName='guest';
		if(!Yii::app()->user->isGuest)
			$userName=!Yii::app()->user->name;
		$date=date("Y-m-d H:i:s");
		$model->attributes=array('user_name'=>$userName,'message'=>$params['msg'],'created'=>$date);
		if($model->validate()){
			$model->save();	        
			echo json_encode(array('user'=>$model->user_name,'msg'=>$model->message,'date'=>$model->created));
		}	
      
    }
	static function getMsg($params)
    {
        $arrData = $params['model']::model()->findAll(array('limit'=>15,'order'=>'created DESC'));
		$arr=array();
		foreach($arrData as $data){
			array_push($arr,$data->attributes);	
		}
		echo json_encode($arr);
    }

}

?>