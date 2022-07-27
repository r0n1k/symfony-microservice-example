<?php


namespace App\Services;


class ServicesUrlManager
{

   /**
    * @var string
    */
   private string $conclusionsServiceUrl;
   /**
    * @var string
    */
   private string $elexpApiUrl;
   /**
    * @var string
    */
   private string $websocketUrl;

   public function __construct(string $conclusionsServiceUrl, string $elexpApiUrl, string $websocketUrl)
   {
      $this->conclusionsServiceUrl = $conclusionsServiceUrl;
      $this->elexpApiUrl = $elexpApiUrl;
      $this->websocketUrl = $websocketUrl;
   }

   public function conclusionsServiceUrl(): string
   {
      return $this->conclusionsServiceUrl;
   }

   public function elexpApiUrl()
   {
      return $this->elexpApiUrl;
   }

    public function websocketUrl()
    {
       return $this->websocketUrl;
    }

}
