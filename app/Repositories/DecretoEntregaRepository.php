<?php
declare(strict_types=1);
namespace App\Repositories;
use App\Core\Database;
class DecretoEntregaRepository {
 public function porDecreto(int $id): array {$s=Database::connection()->prepare('SELECT e.*,t.nome tipo_ajuda_nome,t.unidade_medida FROM decreto_entregas e INNER JOIN tipos_ajuda t ON t.id=e.tipo_ajuda_id WHERE e.desastre_id=:id ORDER BY e.data_entrega DESC,e.id DESC');$s->execute(['id'=>$id]);return $s->fetchAll();}
 public function substituir(int $id,array $itens):void{$p=Database::connection();$s=$p->prepare('DELETE FROM decreto_entregas WHERE desastre_id=:id');$s->execute(['id'=>$id]);$s=$p->prepare('INSERT INTO decreto_entregas (desastre_id,tipo_ajuda_id,quantidade,valor_total,data_entrega) VALUES (:desastre,:tipo,:quantidade,:valor,:data)');foreach($itens as $item){$s->execute(['desastre'=>$id,'tipo'=>$item['tipo_ajuda_id'],'quantidade'=>$item['quantidade'],'valor'=>$item['valor_total'],'data'=>$item['data_entrega']]);}}
}
