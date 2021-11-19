<?php

namespace App\Controller;


use Psr\Log\LoggerInterface;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\DateTime;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * @Route("/")
 */
class TicketController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @Route("/", name="ticket_index", methods={"GET"})
     */
    public function index(TicketRepository $ticketRepository): Response
    {
        $form = $this->createFormBuilder()
        ->add('id' ,TextType::class)
        ->add('Submit', SubmitType::class)
        ->getForm();
        $form2 = $this->createFormBuilder()
        ->add('statut' ,TextType::class)
        ->add('Submit', SubmitType::class)
        ->getForm();
        $form3 = $this->createFormBuilder()
        ->add('date-debut' ,DateType::class, [
            'widget' => 'single_text',
            // this is actually the default format for single_text
            'format' => 'yyyy-MM-dd', ])
        ->add('date-fin' ,DateType::class, [
            'widget' => 'single_text',
            // this is actually the default format for single_text
            'format' => 'yyyy-MM-dd',
        ])
        ->add('Submit', SubmitType::class)
        ->getForm();
        return $this->render('ticket/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'form3' => $form3->createView()

        ]);
    }

    /**
     * @Route("/filterbydate", name="filterbydate", methods={"GET", "POST"})
     */
    public function filterbydate(TicketRepository $ticketRepository, Request $request): Response
    {
        
           
            
                           
        if($request->isMethod('POST')){
            $formdata = $request->request->get('form');
            $datedebut = $formdata['date-debut'];
            $datefin = $formdata['date-fin'];
            $datedebut1 =  new \DateTime($datedebut);   
            $datedebut2 =  new \DateTime($datefin) ;
            $list = $ticketRepository->findByDate($datedebut1,$datedebut2);
            #$this->logger->info($datedebut1);
          
            
            return $this->render('ticket/filterbydate.html.twig', [
                'tickets' => $list
               
            ]);
        }
            
           

        
    }
   /**
     * @Route("/filterbyid", name="filterbyid", methods={"GET", "POST"})
     */
    public function filterbyid(TicketRepository $ticketRepository, Request $request): Response
    {
        
           
            
            if($request->isMethod('POST')){
                $formdata = $request->request->get('form');
                $id = $formdata['id'];
                $list = $ticketRepository->find(intval($id));
                
                
               

                
                return $this->render('ticket/filterbyid.html.twig', [
                    'ticket' => $list
                   
                ]);
            }
            
           

        
    }
     /**
     * @Route("/filterbystatut", name="filterbystatut", methods={"GET", "POST"})
     */
    public function filterbystatut(TicketRepository $ticketRepository, Request $request): Response
    {
                        
        if($request->isMethod('POST')){
            $formdata = $request->request->get('form');
            $statut = $formdata['statut'];
            $list = $ticketRepository->findBy(['statut' => $statut]);
            
            
            //$this->logger->info('\n **********************id************************');
          

            
            return $this->render('ticket/filterbydate.html.twig', [
                'tickets' => $list
               
            ]);
        }
            
    
        
    }

  

    /**
     * @Route("/new", name="ticket_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setStatut('en attente');
            $ticket->setDate(new \DateTime());
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_show", methods={"GET"})
     */
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ticket_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_delete", methods={"POST"})
     */
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
