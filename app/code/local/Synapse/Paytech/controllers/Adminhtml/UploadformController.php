<?php 
class Synapse_Paytech_Adminhtml_UploadformController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction()
	{
		$this->loadLayout()->renderLayout();	
	}
	public function uploadAction()
    {	
		$extension=explode('.',$_FILES['uploadlicense']['name']);
		$extension=strtolower($extension[count($extension)-1]);
		if(!empty($_FILES['uploadlicense'])){
			if(isset($_FILES['uploadlicense']['name']) && $extension=='csv'){
				$fileName = "masterlicensefile.csv"; 
				$fileTmpLoc = $_FILES["uploadlicense"]["tmp_name"];
				$pathAndName = Mage::getBaseDir('media')."/licensemasterfile/".$fileName;			
				$moveResult = move_uploaded_file($fileTmpLoc, $pathAndName);
				if ($moveResult) {
			Mage::getSingleton('adminhtml/session')->addSuccess('License Master file has been uploaded successfully.'); 
			$this->_redirect('*/*');
				} else {
			Mage::getSingleton('adminhtml/session')->addError("ERROR: File not moved correctly");
			$this->_redirect('*/*');
				}
			}else{
				Mage::getSingleton('adminhtml/session')->addError("Please upload a valid license master file");
				$this->_redirect('*/*');
			}			
		}		
      }
}
