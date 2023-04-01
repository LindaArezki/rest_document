<?php
include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {
	
    public $login="tfwbgjkx_mediatekuser"; 
    public $mdp="P@sswordbddmediatek01!"; 
    public $bd="tfwbgjkx_mediatek"; 
    public $serveur="localhost"; 
    public $port="3306"; 	
    public $conn = null; 

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct(){
        try{
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table){
        if($this->conn != null){
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "exemplaire" :
                    return $this->selectAllExemplairesRevue();
                case "commandedocument" :
                    return $this->selectAllCommandesDocument();
                case "abonnement" :
                    return $this->selectFinAbonnement();
                default:
                    // cas d'un select portant sur une table simple, avec tri sur le libellé
                    return $this->selectAllTableSimple($table);
            }			
        }else{
            return null;
        }
    }

    /**
     * récupération d'une ligne d'une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à récupérer
     * @return ligne de la requete correspondant à l'id
     */	
    public function selectOne($table, $id){
        if($this->conn != null){
            switch($table){
                case "exemplaire" :
                    return $this->selectAllExemplairesRevue($id);
                case "commandedocument" :
                    return $this->selectAllCommandesDocument($id);
                case "abonnement" :
                     return $this->selectAllCommandesRevue($id);
                case "utilisateur" :
                     return $this->selectUtilisateur($id);
                default:
                    // cas d'un select portant sur une table simple			
                    $param = array(
                        "id" => $id
                    );
                    return $this->conn->query("select * from $table where id=:id;", $param);					
            }				
        }else{
                return null;
        }
    }

   
    /**
     * récupération de toutes les lignes de d'une table simple (sans jointure) avec tri sur le libellé
     * @param type $table
     * @return lignes de la requete
     */
    public function selectAllTableSimple($table){
        $req = "select * from $table order by libelle;";		
        return $this->conn->queryAll($req);		
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres(){
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";		
        return $this->conn->queryAll($req);
    }	

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd(){
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";	
        return $this->conn->queryAll($req);
    }	

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues(){
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->queryAll($req);
    }	
    
    /**
     * récupére les abonnements se terminant
     * @return lignes de la requete
     */
    public function selectFinAbonnement(){
     $req ="SELECT a.dateFinAbonnement, a.idRevue, d.titre ";
		$req .="FROM abonnement a ";
		$req .="JOIN revue r ON a.idRevue = r.id ";
		$req .="JOIN document d ON r.id = d.id ";
		$req .="WHERE DATEDIFF(CURRENT_DATE(), a.dateFinAbonnement) < 30 ";
		$req .="ORDER BY a.dateFinAbonnement ASC; ";
    return $this->conn->queryAll($req);
    }	
    

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectAllExemplairesRevue($id){
        $param = array(
                "id" => $id
        );
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id =:id ";
        $req .= "order by e.dateAchat DESC";		
        return $this->conn->queryAll($req, $param);
    }		

    /**
     * récupération de toutes les commandes d'un document
     * @param string $id id de la commande
     * @return lignes de la requete
     */
    public function selectAllCommandesDocument($id)
    {
        $param = array(
                "id" => $id
        );
        $req = "Select c.id, c.dateCommande, c.montant, cd.nbExemplaire, cd.idLivreDvd ,cd.suivi, s.libelle ";
        $req .= "FROM commande c join commandedocument cd ON c.id= cd.id ";
        $req .= "join suivi s on cd.suivi = s.idetape ";
        $req .= "where cd.idLivreDvd = :id ";
        $req .= "order by c.dateCommande DESC ";
        return $this->conn->queryAll($req, $param);
    }	
    
    /**
     * récupération de toutes les commandes revue
     * @param string $id id de la commande
     * @return lignes de la requete
     */
    public function selectAllCommandesRevue($id)
    {
        $param = array(
                "id" => $id
        );
        $req = "SELECT c.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue  ";
        $req .= "FROM commande c JOIN abonnement a ON c.id=a.id ";
        $req .= "WHERE a.idRevue= :id  ";
        $req .= "order by c.dateCommande DESC  ";
        return $this->conn->queryAll($req, $param);
    }	
    
     /**
     * récupération d'un utilisateur
     * @param string $id id de la commande
     * @return lignes de la requete
     */
    public function selectUtilisateur($id)
    {
        $param = array(
                "id" => $id
                
        );
        $req = "SELECT u.login, u.pwd , u.idservice, s.libelle  ";
        $req .= "FROM utilisateur u  JOIN service s ON s.id=u.idService ";
        $req .= "WHERE u.login =:id  ";     
        return $this->conn->queryAll($req, $param);
    }	
    
    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */	
    public function delete($table, $champs){
        if($this->conn != null){
            // construction de la requête
            $requete = "delete from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);   
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }
    
    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */	
    public function insertOne($table, $champs){
        if($this->conn != null && $champs != null){
            // construction de la requête
            $requete = "insert into $table (";
            foreach ($champs as $key => $value){
                $requete .= "$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ") values (";
            foreach ($champs as $key => $value){
                $requete .= ":$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);
            $requete .= ");";	
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }
    
    

    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $param nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */	
    public function updateOne($table, $id, $champs){
        if($this->conn != null && $champs != null){
            // construction de la requête
            $requete = "update $table set ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key,";
            }
            // (enlève la dernière virgule)
            $requete = substr($requete, 0, strlen($requete)-1);				
            $champs["id"] = $id;
            $requete .= " where id=:id;";				
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

}