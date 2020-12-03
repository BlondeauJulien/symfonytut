<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{

  private $session;
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
  {
    $this->session = $session;
    $this->entityManager = $entityManager;
  }

  public function add($id)
  {
    $cart = $this->get('cart', []);

    if( !empty($cart[$id]) ) {
      $cart[$id]++;
    } else {
      $cart[$id] = 1;
    }

    $this->session->set('cart', $cart);
  }

  public function decrease($id)
  {
    $cart = $this->get('cart', []);

    if( $cart[$id] > 1 ) {
      $cart[$id]--;
      return $this->session->set('cart', $cart);
    } else {
      $this->delete($id);
    }

  }

  public function get()
  {
    return $this->session->get('cart');
  }

  public function remove()
  {
    return $this->session->remove('cart');
  }

  public function delete($id)
  {
    $cart = $this->get('cart', []);

    unset($cart[$id]);

    return $this->session->set('cart', $cart);
  }

  public function getFull()
  {
    $cartComplete = [];

    if($this->get()) {
      foreach($this->get() as $id => $quantity) {

        // User can't add random id in the cart
        $product_object = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
        if(!$product_object) {
          $this->delete($id);
          continue;
        }

        $cartComplete[] = [
          'product' => $product_object,
          'quantity' => $quantity
        ];
      }
    }

    return $cartComplete;
  }
}