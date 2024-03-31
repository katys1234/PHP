<?php
require_once ("common/page.php");
require_once ("common/a_content.php");
require_once ("common/simplexlsx.class.php");


class pagination {
    private $objects_count;
    private $objects_per_page;
    public function __construct(int $objects_count, int $objects_per_page) {
        $this->objects_count=$objects_count;
        $this->objects_per_page=$objects_per_page;

    }

    public function get_objects_idx_by(int $page_num):array | null {
        $start=min(($page_num-1)*$this->objects_per_page, $this->objects_count);

        $end=min($start+$this->objects_per_page, $this->objects_count)-1;
        if ($start<0 || $start>$end) {
            return null;
        }
        return array($start,$end);
    }

    private function get_page_count(): int{
        return $this->objects_count / $this->objects_per_page +
            ($this->objects_count % $this->objects_per_page != 0);
    }

    public function get_pages(string $url_template){
        $max_pages = $this->get_page_count();
        $result = array();
        for ($i = 1; $i <= min(3, $max_pages); $i++){
            $url = $url_template."$i";
            $result[] = array($i, $url);
        }
    }

}
class the_content extends \common\a_content {
    private string $user_files_dir = "user_files/";
    public function show_content(): void{
        $name = $this->get_file();
        ?>

        <form action="pages.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="1024000">
            <input type="file" name="userfile">
            <input type="submit" value="Отправить">
        </form>
        <?php
        if ($name !== null && $xlsx = SimpleXLSX::parse($name) ) {
            echo '<table>';
            foreach( $xlsx->rows() as $r ) {
                echo '<tr><td style="border: 1px solid black">'.implode('</td><td style="border: 1px solid black">', $r ).'</td></tr>';
            }
            echo '</table>';
            } else {
                echo SimpleXLSX::parse_error();
            }
    }

    private function get_file(): string | null
    {
        if (!isset($_FILES['userfile'])) return null;
        $name = $this->user_files_dir.basename($_FILES['userfile']['name']);
        if (@move_uploaded_file($_FILES['userfile']['tmp_name'], $name)) return $name;
        return null;
    }
}

$content = new the_content();
new \common\page($content);