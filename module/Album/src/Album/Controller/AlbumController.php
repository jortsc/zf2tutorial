<?php 
namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album;          // <-- Add this import
use Album\Form\AlbumForm;       // <-- Add this import


class AlbumController extends AbstractActionController
{
    protected $albumTable;
    
    public function indexAction()
    {
         return new ViewModel(array(
            'albums' => $this->getAlbumTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        //AlbumForm() Es la calse que se encarga de definir los elementos del form y sus atributos y validaciones
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());
            /*
             * If the Request object’s isPost() method is true, then the form has
             *  been submitted and so we set the form’s input filter from an album
             *  instance. We then set the posted data to the form and check to see
             *  if it is valid using the isValid() member function of the form.
             */
            if ($form->isValid()) {
                $album->exchangeArray($form->getData());
                //If the form is valid, then we grab the data from the form and store to the model using saveAlbum().
                $this->getAlbumTable()->saveAlbum($album);
                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }
        /*
         * return the variables that we want assigned to the view. In this case,
         *  just the form object. Note that Zend Framework 2 also allows you to 
         * simply return an array containing the variables to be assigned to the
         *  view and it will create a ViewModel behind the scenes for you. 
         * This saves a little typing
         */
        return array('form' => $form);
    }

    public function editAction()
    {
        //params is a controller plugin that provides a convenient way 
        //to retrieve parameters from the matched route
        //We use it to retrieve the id from the route we created in the modules’ module.
        //config.php. If the id is zero, then we redirect to the add action, 
        //otherwise, we continue by getting the album entity from the database.
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album', array(
                'action' => 'add'
            ));
        }
        $album = $this->getAlbumTable()->getAlbum($id);
         //AlbumForm() Es la calse que se encarga de definir los elementos del form y sus atributos y validaciones
        $form  = new AlbumForm();
        /*
         * The form’s bind() method attaches the model to the form. This is used in two ways:
         * When displaying the form, the initial values for each element are extracted from the model.
         * After successful validation in isValid(), the data from the form is put back into the model.
         */
        $form->bind($album);
        /*
         * These operations are done using a hydrator object. There are a number of hydrators,
         *  but the default one is Zend\Stdlib\Hydrator\ArraySerializable which expects to find
         *  two methods in the model: getArrayCopy() and exchangeArray().
         *  We have already written exchangeArray() in our Album entity,
         *  so just need to write getArrayCopy():
         */
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAlbumTable()->saveAlbum($form->getData());

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }
        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deleteAction()
    {
       $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getAlbumTable()->deleteAlbum($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }
        return array(
            'id'    => $id,
            'album' => $this->getAlbumTable()->getAlbum($id)
        );
    }
    
    public function getAlbumTable()
    {
        if (!$this->albumTable) {
            $sm = $this->getServiceLocator();
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }
        return $this->albumTable;
    }
}