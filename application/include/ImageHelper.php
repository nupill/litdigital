<?php
require_once(dirname(__FILE__) . '/../config/general.php');

class ImageHelper {
    
    public static function image_upload($image, $upload_path, $thumb_width = 300) {
        
        if (!$image || $image['error']) {
            return json_encode(array('error' => 'Imagem inválida'));
        }
        
        if (!is_numeric($thumb_width)) {
            return json_encode(array('error' => 'Largura deve ser numérica'));
        }
        
        $file_basename = '';
        $file_thumb_basename = '';
        $temp_file = $image['tmp_name'];
        $file_info = pathinfo($image['name']);
        $file_hash = file_hash($temp_file);
        $file_basename = $file_hash . '.' . $file_info['extension'];
        $file_thumb_basename = $file_hash . '_thumb.' . $file_info['extension'];
        $target_file = $upload_path . $file_basename;
        $target_thumb_file = $upload_path . $file_thumb_basename;
        
        if (!is_dir($upload_path)) {
        	if (!@mkdir($upload_path, 0755)) {
        	    Logger::log('Erro ao criar o diretório de uploads ('.$upload_path.')', __FILE__);
        		return json_encode(array('error' => 'Erro ao criar o diretório de uploads'));
        	}
        }
        if (!is_writable($upload_path)) {
        	if (!@chmod($upload_path, 0755)) {
        	    Logger::log('Diretório de uploads não tem permissão de escrita ('.$upload_path.')', __FILE__);
        		return json_encode(array('error' => 'Erro alterar permissões do diretório de uploads'));
        	}
        }
        
        //Doesn't override the file
        if (!file_exists($target_file)) {
            if (!in_array(strtolower($file_info['extension']), array('jpg', 'jpeg', 'png', 'gif'))) {
                return json_encode(array('error' => 'Formato de arquivo inválido'));
            }
            if (!@move_uploaded_file($temp_file, $target_file)) {
                Logger::log("Erro ao mover arquivo de upload (DE: $temp_file - PARA: $target_file", __FILE__);
            	return json_encode(array('error' => 'Erro ao salvar a foto no servidor'));
            } 
        }
        //Doesn't override the file (thumbnail)
        if (!file_exists($target_thumb_file)) {
            if (!create_thumbnail($target_file, $thumb_width)) {
                Logger::log("Erro criar a miniatura da foto ($target_file)", __FILE__);
        	    return json_encode(array('error' => 'Erro ao criar a miniatura da foto'));
            }
        }
        return array('image' => $file_basename, 'thumb' => $file_thumb_basename);
    }
}