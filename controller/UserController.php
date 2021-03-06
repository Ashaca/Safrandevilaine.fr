<?php
class UserController extends Controller{

	public function __construct($request){
		parent::__construct($request);
		$this->loadModel('User');

		$this->tpl->assign('combo_pays',array('France'=>'France',"United Kingdom"=>"United Kingdom",'Autre'=>'Autre'));
	}

	/**
	*	Permet la création d'un nouvel utilisateur par un invité
	**/
	public function add(){
		$user = new stdClass();
		$user->created= date('Y-m-d H:i:s');
		$this->tpl->assign('user',$user);
	}


	/**
	* Fonction permettant l'affichage des actualités en vue de leur gestion
	**/
	function admin_index() {
		$users = $this->User->find();
		if (empty($users)){
			$this->setInfos ('Aucun utilisateur en base de données', 'info');
		}
		$this->infos();
		$this->tpl->assign('users',$users);	
	}

	/**
	*	Permet d'ajouter un nouvel utilisateur
	**/
	public function admin_add(){
		$user = new stdClass();
		$user->created= date('Y-m-d H:i:s');
		$this->tpl->assign('user',$user);
	}

	/**
	*	Permet d'éditer un utilisateur
	**/
	public function admin_edit($id=null){
		if($this->request->data){
			//formatage des données
			$this->request->data->nom=strtoupper($this->request->data->nom);
			$this->request->data->prenom=ucfirst(strtolower($this->request->data->prenom));
			$this->request->data->adresse=ucfirst($this->request->data->adresse);
			$this->request->data->cp=strtoupper($this->request->data->cp);
			$this->request->data->ville=strtoupper($this->request->data->ville);
			$this->request->data->email=strtolower($this->request->data->email);
			
			//demande de sauvegarde des nouvelles données
			$result= $this->User->save($this->request->data);

			//Gestion des messages de validation /invalidation
			if ($result===true){
				if ($id){
					$this->setInfos ('L\'utilisateur "'.$this->request->data->prenom.' '.$this->request->data->nom.'" a bien été modifié', 'success');
				}else{
					$this->setInfos ('L\'utilisateur "'.$this->request->data->prenom.' '.$this->request->data->nom.'" a bien été créé', 'success');
					$id=$this->User->lastEntryId();
				}
				
			} else {
				foreach ($result as $k=>$v){
					$this->setInfos ($v, 'info');
				}
				if ($id){
					$this->setInfos ('Erreur : L\'utilisateur "'.$this->request->data->prenom.' '.$this->request->data->nom.'" n\'a pas été modifié', 'error');
				}else{
					$this->setInfos ('Erreur : L\'utilisateur "'.$this->request->data->prenom.' '.$this->request->data->nom.'" n\'a pas été créé', 'error');
				}
			}
		}

		//Remplissage auto des champs si des données sont disponibles (BDD ou data page précédente)
		if ($id){
			$user= $this->User->findFirst(array('conditions'=> array('id'=>$id)));
			$user->pwd2=$user->pwd;	
		} else {
			$user=$this->request->data;
			$user->id=null;
		}

		$this->infos();
		$this->tpl->assign('user',$user);
	}

	/**
	*	Permet la suppression d'un utilisateur
	**/
	public function admin_delete($id, $userName){
		if ($this->User->delete($id)){
			$this->setInfos('L\'utilisateur "'.$userName.'" a bien été supprimée de la base de donnée', 'success');			
		}
		else {
			$this->setInfos('Erreur : L\'utilisateur "'.$userName.'" n\'a pas été supprimée', 'error');
		}
		$this->redirect('admin/user/index');
	}
}

?>