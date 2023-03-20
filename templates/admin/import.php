<?php
/** @var array $args */
?>
<div class="wrap">
    <form method="post" action="" name="wyv-form-import">
        <h1 class="wp-heading-inline">Импорт</h1>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><label for="wyv-form-link">Ссылка на канал</label></th>
                <td><input type="text" size="100" name="wyv-form-link" id="wyv-form-link" value="https://www.youtube.com/@AstrakhanRuTV" class="regular-text"></td>
            </tr>
            <tr>
                <td><input type="submit" name="wyv-form-submit" value="Запустить" class="page-title-action"></td>
            </tr>
            </tbody>
        </table>
    </form>
</div>

<?php
foreach ( $args as $arg ) {
	echo $arg."<br>";
}
