1. Import database structure from demos/blog/protected/data/schema.chat.sql

2. in view: 
	this->widget('ChatW',array(
		'id'=>'chat id',
		'model'=>'model',- model must be have fields : id, username, message, created
		'ajaxUrl'=>'/site/ChatAjax' - ajax action
		));

3. in controller:
	public function actionChatAjax()
	{
		Yii::import('application.extensions.chat.ChatW');
	    	ChatW::Ajax();
	}