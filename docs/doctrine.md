#### Cache Second Level

```
READ_ONLY, o valor padrão. Permite que as entidades em cache sejam apenas lidas e não modificadas. É a forma mais performática, mas é útil apenas para entidades que são apenas para leitura.
NONSTRICT_READ_WRITE. Permite que as entidades possam ser alteradas, mas não possui controle de “lock”.
READ_WRITE. O modo mais completo, permite alterações e faz um controle mais seguro do acesso às entidades evitando conflitos. Mas para isso perde um pouco da performance, e o sistema de cache selecionado precisa permitir o recurso de locks.
```
