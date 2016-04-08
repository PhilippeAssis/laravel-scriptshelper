# Laravel Scripts Helper

Gerenciador de scripts (jquery, javascript...) para laravel. Simplifica e organiza a mono de aplicar scripts as views.

## Instalação

Via composer
```shell
composer required wiidoo/scriptshelper
```

Por padrão o diretório de modelos de script é `resources/scripts`, isso pode ser alterado no arquivo de configurações.

Crie o diretório de arquivos carregados
```shell
mkdir resources/scripts
```

O blader pode ser usado. Para evitar erros, é necessário criar um diretório de compilamento. Por padrão, usamos `storage/scripts`.

```shell
mkdir storage/scripts
```

É necessário permissão de leitura e escrita para esse diretório.

## Arquivo de configuração
Você pode alterar as configurações padrões dessa biblioteca em `config/wiidoo.php` (`Illuminate\Support\Facades\Config::get("wiidoo.scripts")`). Nesse arquivo, você pode criar valores padrões para todas as propriedades tanto publicas (`public`) como protegidas (`protected`) das classes dessa biblioteca.

Exemplo:
```php
<?php

return [
    'scripts' => [
        'templatePath' => resource_path('scripts'), // padrão
        'compiled' => storage_path('scripts') // padrão
    ]
];
```

## scriptsHelper.php

| Função         | Descrição                                                               |
|----------------|-------------------------------------------------------------------------|
| scriptHelper() | Retorna a variável global `$SCRIPTSHELPERCLASS`, usada em `Wiidoo\ScriptsHelper\Lib\Scripts` |

## Tutorial
 Vamos partir de que estamos fazendo uma view de upload de imagens e usaremos o [picZone](https://github.com/PhilippeAssis/picZone) para isso.
 
 Primeiro teremos de criar os templates para nosso script, isso inclui o input (o objeto que será chamado pela nosso script) e a declaração de nosso script.
 
### O input
Em `resources/scripts` vamos criar um arquivo chamados `piczone.input.blade.php`, com o seguinte conteúdo:
```html
<input id="{{ $id }}" type="file" value="" class="{{ $class }}">
```

Esse será nosso modelo para input do nosso arquivo. Aqui poderiamos ter qualquer outra tag de declaração que trabalhe com nosso script.

Agora vamos incorpora-lo... 
Após incorporar os arquivos `css` e `js` no nosso layout, vamos criar um formulário da seguinte maneira:
 
 ```html
 <form action="" method="post" enctype="multipart/form-data">
    {!! scriptHelper()->type('piczone')->input() !!}
    <button type="submit">Submit</button>
</form>
 ```
 
Você pode executar a view para ver o que aconteceu. Se não ocorreu nenhum erro, você vera um `input file` na sua página.
 
### O script
 Agora vamos criar em `resources/scripts` nosso arquivo de script, chamaremos ele de `piczone.script.blade.php`.
 
 ```html
 <script>
    $(function () {
        $('#{{$id}}').picZone( {{ json_encode($params) }}  )
    })
</script>
 ```
Nesse arquivo estaremos retornando incorporando nosso plugin de jquery com os dados gerados no exemplo anterior.

Vamos por isso na nossa view... Veja um exemplo mais completo com a instrução `script()` presente.
 
```html
<html>
<head>
    <link rel="stylesheet" href="bower_components/piczone/css/piczone.css">
</head>
<body>

<formmethod="post" enctype="multipart/form-data">
    {!! scriptHelper()->type('piczone')->input() !!}
    <button type="submit">Submit</button>
</form>

<script type="text/javascript" src="bower_components/jquery-dist/jquery.min.js"></script>
<script type="text/javascript" src="bower_components/piczone/i18n/en.i18n.piczone.js"></script>
<script type="text/javascript" src="bower_components/piczone/js/piczone.js"></script>
{!! scriptHelper()->script() !!}

</body>
</html>
```

Pronto! Nosso helper gerará uma ID única para esse script.

#### Passando parametros
Podemos passar paramentros customizados para nosso `piczone.input.blade.php` ou para `piczone.script.blade.php` tranquilamente, segue o exemplo:
```php
scriptHelper()->type('piczone')->class('picZone')->camera(false)->lang('pt_BR')->widthMax(600)->keepCalm('beba um café')->input()
```

O resultado do exemplo acima será
```html
<!-- scriptHelper()->type('piczone')->class('picZone')->camera(false)->lang('pt_BR')->widthMax(600)->keepCalm('beba um café')->input() -->
<input id="piczone-0" type="file" class="picZone">

<!-- scriptHelper()->script() -->
<script>
    $(function () {
        $('#piczone-0').picZone({
        'camera' : false,
        'lang' : 'pt_BR',
        'widthMax' : 600,
        'keepCalm' : 'beba um café'
        })
    })
</script>
```

Sim, isso é meio mágico. o `scriptHelper` pegou os metodos que você declarou e jogou tudo na variavel `$params`, assim pode tratar diretamente do PHP as instruções de seu javascript. Outro exemplo:

`piczone.script.blade.php`
```html
<script>
    $(function () {
        $('#{{ $id }}').picZone({
            'camera' : {{ (isset($camera) and $camera)  ? 'true' : 'false' }},
            'lang' : {{ (isset($ptbr) and $ptbr)  ? 'pt_BR' : 'en' }}
        })
    })
</script>
```
`index.blade.php`
```html
<!-- scriptHelper()->type('piczone')->camera(false)->ptbr(true)->input() -->
<input id="piczone-0" type="file">

<!-- scriptHelper()->script() -->
<script>
    $(function () {
        $('#piczone-0').picZone({
        'camera' : false,
        'lang' : 'pt_BR'
        })
    })
</script>
```
Nesse exemplo criamos sem nenhuma pre-definição o metodo ptbr(), e passamos true, tratamos em `piczone.script.blade.php` e pronto.
 