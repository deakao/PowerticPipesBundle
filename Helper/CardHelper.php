<?php 
namespace MauticPlugin\PowerticPipesBundle\Helper;

class CardHelper 
{

    protected $translator;

  public function __construct($translator)
  {
    $this->translator = $translator;
  }

  public function getStuckSince($date_to, $date_from)
  {
    $date_to = new \DateTime($date_to);
    $date_from = new \DateTime($date_from);
    $diff = $date_to->diff($date_from);
    if($diff->y > 0){
      return $diff->y . ' '.($diff->y > 1 ? $this->translator->trans('plugin.powerticpipes.card.years') : $this->translator->trans('plugin.powerticpipes.card.year'));
    } else if($diff->m > 0){
      return $diff->m.' '.($diff->m > 1 ? $this->translator->trans('plugin.powerticpipes.card.months') : $this->translator->trans('plugin.powerticpipes.card.month'));
    } else if($diff->d > 0){
      return $diff->d . ' '.($diff->d > 1 ? $this->translator->trans('plugin.powerticpipes.card.days') : $this->translator->trans('plugin.powerticpipes.card.day')) ;
    } else if($diff->h > 0){
      return $diff->h . ' '.($diff->h > 1 ? $this->translator->trans('plugin.powerticpipes.card.hours') : $this->translator->trans('plugin.powerticpipes.card.hour'));
    } else if($diff->i > 0){
      return $diff->i . ' '.($diff->i > 1 ? $this->translator->trans('plugin.powerticpipes.card.minutes') : $this->translator->trans('plugin.powerticpipes.card.minute'));
    } else {
      return $this->translator->trans('plugin.powerticpipes.card.now');
    }
  }
}