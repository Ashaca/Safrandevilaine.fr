<?php
class ActualiteController extends Controller{

	public function __construct($request){
		parent::__construct($request);
		$this->loadModel('Actualite');
	}

	/* 
	*	fonction permettant d'afficher les actualités avec pagination
	*/
	function index() {
		$perPage = 10;
		$maxLength = 300;
		$maxLinkPage = 10;
		$conditions['conditions']=array(
			'online'=>1
			);
		$nbPost = $this->Actualite->findCount($conditions);
		$conditions['limit'] = ($perPage*($this->request->page-1)).','.$perPage;
		$nbPages= ceil($nbPost/$perPage);
		$actualites = $this->Actualite->find($conditions);
		if (empty($actualites)){
			$this->e404('Il n\'y a aucune actualité pour le moment');
		}

		foreach ($actualites as $k) {
			if (strlen($k->content) >=$maxLength){
				$k->content= substr($k->content,0, $maxLength);
			}
		}
		$startLinkPage= (($this->request->page-$maxLinkPage)<1) ? 1 : $this->request->page-$maxLinkPage ;
		$endLinkPage= ($this->request->page+($maxLinkPage-1)>=$nbPages) ? $nbPages : $this->request->page+($maxLinkPage-1);
		
		$this->tpl->assign('startLinkPage', $startLinkPage);
		$this->tpl->assign('endLinkPage', $endLinkPage);
 		$this->tpl->assign('actualites',$actualites);	
	}

	function view($id){
		$conditions['conditions']= array(
			'id'=>$id,
			'online'=> 1
			);
		$page = $this->Actualite->findFirst($conditions);
		if (empty($page)){
 			$this->e404('Erreur : Page <em>'.$id.'</em> introuvable');
 		}
 		$this->tpl->assign('page',$page);	
	}

	/**
	* Fonction permettant l'affichage des actualités en vue de leur gestion
	**/
	function admin_index() {
		$actualites = $this->Actualite->find();
		if (empty($actualites)){
			$this->setInfos ('Aucune actualité en base de données', 'info');
		}
		$this->infos();
		$this->tpl->assign('actualites',$actualites);	
	}

	/**
	*	Permet de prévisualiser une actualité avant sa publication
	**/
	function admin_view($id=NULL){
		if (isset($id)) {
			$conditions = is_numeric($id) ? array('conditions'=>array('id'=>$id)) : array('conditions'=>array('slug'=>$id));
			$actualite = $this->Actualite->findFirst($conditions);
			if (empty($actualite)){
 				$this->e404('Erreur : Page <em>'.$id.'</em> introuvable');
 			}
	 		$this->tpl->assign('actualite',$actualite);	
		}
 		else $this->e404('Erreur : Page introuvable (id null)');

	}

	/**
	*	Permet d'ajouter une nouvelle actualité
	**/
	public function admin_add(){
		$this->addTinyMCE();
		$actualite = new stdClass();
		$actualite->created= date('Y-m-d H:i:s');
		$this->tpl->assign('actualite',$actualite);
	}

	/**
	*	Permet d'éditer une actualité
	**/
	public function admin_edit($id=null){
		$this->addTinyMCE();
		if($this->request->data){
			//demande de sauvegarde des nouvelles données
			$result= $this->Actualite->save($this->request->data);

			//Gestion des messages de validation /invalidation
			if ($result===true){
				if ($id){
					$this->setInfos ('L\'actualité "'.$this->request->data->name.'" a bien été modifiée', 'success');
				}else{
					$this->setInfos ('L\'actualité "'.$this->request->data->name.'" a bien été créée', 'success');
					$id=$this->Actualite->lastEntryId();
				}
				
			} else {
				foreach ($result as $k=>$v){
					$this->setInfos ($v, 'info');
				}
				if ($id){
					$this->setInfos ('Erreur : L\'actualité "'.$this->request->data->name.'" n\'a pas été modifiée', 'error');
				}else{
					$this->setInfos ('Erreur : L\'actualité "'.$this->request->data->name.'" n\'a pas été créée', 'error');
				}
			}
		}

		//Remplissage auto des champs si des données sont disponibles (BDD ou data page précédente)
		if ($id){
			$actualite= $this->Actualite->findFirst(array('conditions'=> array('id'=>$id)));	
		} else {
			$actualite=$this->request->data;
			$actualite->id=null;
		}

		$this->infos();
		$this->tpl->assign('actualite',$actualite);
	}

	/**
	*	Permet la suppression d'une actualité
	**/
	public function admin_delete($id,$slug){
		if ($this->Actualite->delete($id)){
			$this->setInfos('L\'actualité "'.$slug.'" a bien été supprimée de la base de donnée', 'success');			
		}
		else {
			$this->setInfos('Erreur : L\'actualité "'.$slug.'" n\'a pas été supprimée', 'error');
		}
		$this->redirect('admin/actualite/index');
	}
}
?>