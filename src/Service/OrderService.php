<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderService
{
    private $session;
    private $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function add(int $id)
    {
        $order = $this->session->get('order', []);

        if (!empty($order[$id])) {
            $order[$id]++;
        } else {
            $order[$id] = 1;
        }

        $this->session->set('order', $order);
    }

    public function delete(int $id)
    {
        $order = $this->session->get('order', []);

        if (!empty($order[$id])) {
            unset($order[$id]);
        }

        $this->session->set('order', $order);
    }

    public function getFullOrder(): array
    {
        $order = $this->session->get('order', []);

        $orderItems = [];

        foreach ($order as $id => $quantity) {
            $orderItems[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity,
            ];
        }

        return $orderItems;
    }

    public function getTotal()//: float
    {
    }
}
