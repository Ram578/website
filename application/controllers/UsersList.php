<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userslist extends CI_Controller {

	/**
	 * This is TonalTest page controller.
	 * Develope on 21th July'2016 by Hemanth Kumar
	 */
	public function index()
	{
		if(isset($this->session->userdata['EmployeeID']))
		{
			$this->load->model('adminmodel');
			
			$application_type = $_GET['type'];
						
			$arrData['application_type'] = $application_type;
			
			$arrData['Users'] = $this->adminmodel->FetchUsers();

			foreach ($arrData['Users'] as $key => &$value) 
			{
				$intScore = $this->adminmodel->FetchUserResult($value['id']);

				$value['score'] = $intScore;

				$value['certile'] = $this->adminmodel->FetchCertileWRT($intScore, $value['age'], $value['gender'], $application_type);
			}
			
			$this->load->view('userslist', $arrData);
		}
		else
		{
			redirect('/admin', 'refresh');
		}

	 }

	public function export()
	{
		$this->load->model('adminmodel');
		
		 // $application_type = $_GET['type'];
						
		 // $arrData['application_type'] = $application_type;

		$arrData['Users'] = $this->adminmodel->FetchUsers();
		
		foreach ($arrData['Users'] as $key => &$value) 
		{
			$intScore = $this->adminmodel->FetchUserResult($value['id']);
			
			// Check the Status & then assign the value
			if($value['status'] == 1) 
			{
				$value['status'] = "Next";
			}
			else if($value['status'] == 2)
			{
				$value['status'] = "More Examples";
			} 
			else
			{
				$value['status'] = "";
			}

			$value['score'] = $intScore;

			$value['certile'] = $this->adminmodel->FetchAllCertileWRT($intScore, $value['age'], $value['gender']);
		}
		
		// Enable to download this file
		$filename = "UsersList.csv";
		 
		header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: text/csv");
         
        $display = fopen("php://output", 'w');
         
        $arrHeaders = array( 'Age', 'Gender', 'File Number', 'Created Date', 'Completed Date', 'Score', 'Certile','P1','P2','P3','P4','P5');
        
        fputcsv($display, array_values($arrHeaders), ",", '"');
       
		$users = $arrData['Users'];
		
		if(isset($users))    
		{
            foreach ($users as  $users)
			{
               fputcsv($display, array_values($users), ",", '"');
			}
		}
       
		fclose($display);
	}
}
