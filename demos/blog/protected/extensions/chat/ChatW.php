/*
* Simlpe chat widget 
* js lib jquery.ui.chatbox.js
* for use 
* this->widget('ChatW',array(
*		'id'=>'chat id',
*		'model'=>'model',- model must be have fields : id, username, message, created
*		'ajaxUrl'=>'/site/ChatAjax' - ajax action
*		));
*
*
* in controller 
* public function actionChatAjax()
* {
* 	Yii::import('application.extensions.chat.ChatW');
*    ChatW::Ajax();
* }
*
*
*/
<?php class ChatW extends CInputWidget
{

	private $baseUrl;
    public $id='chat';
    public $model;
	public $ajaxUrl;
  
    public function run()
    {
		/**
		* 
		* register js
		* 
		*/
		
	    $dir = dirname(__FILE__);
        $this->baseUrl = Yii::app()->getAssetManager()->publish($dir);
    	$cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile($this->baseUrl . '/assets/jquery-ui-1.10.3.custom.js');
        $cs->registerScriptFile($this->baseUrl . '/assets/jquery.ui.chatbox.js');
        $cs->registerCssFile($this->baseUrl . '/assets/jquery.ui.chatbox.css');
		echo '<span id = "togle_chat" class="togle-chat"><</span>';
		echo '<ul id="'.$this->id.'"></ul>';
        
	$script='
		  $(document).ready(function(){
	          var box = null;
	          $("#togle_chat").click(function(event, ui) {
	              if(box) {
	                  box.chatbox("option", "boxManager").toggleBox();
					  togleSpan();
	              }
	              else {
	                  box = $("#'.$this->id.'").chatbox({
					  								id:"'.$this->id.'", 
	                                                title : "test chat",
	                                              	messageSent : function(id, user, msg) {
														$.post("'.$this->ajaxUrl.'",{"operation" : "saveMsg", "msg" : msg,"model" : "'.$this->model.'"}, 
											                function (data) {
																$("#"+id).chatbox("option", "boxManager").addMsg(data.user, data.msg, data.date,data.id);
																limitMsg();
											                },"json"
											            );
	                                                }});
					getMessage();	
					togleSpan();							
	              }
				  setInterval(getMessage, 5000);
	          });
			  
	      });
	   function getMessage(){
		   	var id_msg=0;
			if($("#'.$this->id.' li:last").attr("id"))	
				var id_msg=$("#'.$this->id.' li:last").attr("id");
		   	$.post("'.$this->ajaxUrl.'",{"operation" : "getMsg","model" : "'.$this->model.'","id":id_msg}, 
		                function (data) {
							$.each(data, function(i, item) {
								limitMsg();
								$("#'.$this->id.'").chatbox("option", "boxManager").addMsg(item.user_name, item.message, item.created, item.id);						
							});
							
		                },"json"
		            );
	   	
	   }
	   function limitMsg(){
		   	if($("#'.$this->id.' li").size()>15){
				$("#'.$this->id.' li:first").remove();
			}
	   }
	   function togleSpan(){
		   	if( $("#togle_chat").hasClass("open")){
				$("#togle_chat").removeClass("open");	
				$("#togle_chat").text("<")
			} else {
				$("#togle_chat").addClass("open");	
				$("#togle_chat").text(">")
			}
		} 
	';
   $cs->registerScript('chat', $script);
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
			$userName=Yii::app()->user->name;
		$date=date("Y-m-d H:i:s");
		$model->attributes=array('user_name'=>$userName,'message'=>$params['msg'],'created'=>$date);
		if($model->validate()){
			$model->save();	        
			echo json_encode(array('user'=>$model->user_name,'msg'=>$model->message,'date'=>$model->created,'id'=>$model->id));
		}	
      
    }
	static function getMsg($params)
    {
		$id=$params['id'];
		if($id==0)
			$id=$params['model']::model()->find(array('order'=>'id desc','limit'=>1))->id-15;
        $arrData = $params['model']::model()->findAll(array('limit'=>15,'condition'=>'id >'.$id,'order'=>'id > '.$id.' ASC'));
		$arr=array();
		foreach($arrData as $data){
			array_push($arr,$data->attributes);	
		}
		echo json_encode($arr);
    }

}

?>