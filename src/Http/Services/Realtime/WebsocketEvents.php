<?php


namespace App\Http\Services\Realtime;


final class WebsocketEvents
{
   public const CONCLUSION_CREATED = 'conclusion_created';
   public const CONCLUSION_CHANGED = 'conclusion_changed';
   public const CONCLUSION_DELETED = 'conclusion_deleted';

   public const PARAGRAPH_CREATED = 'paragraph_created';
   public const PARAGRAPH_CHANGED = 'paragraph_changed';
   public const PARAGRAPH_DELETED = 'paragraph_deleted';

   public const BLOCK_CREATED = 'block_created';
   public const BLOCK_CHANGED = 'block_changed';
   public const BLOCK_DELETED = 'block_deleted';
}
