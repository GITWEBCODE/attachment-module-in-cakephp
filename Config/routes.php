<?php
Router::connect('/attach/*', array('plugin'=>'Attach','controller' => 'Attachments', 'action' => 'uploadAttach'));;
Router::connect('/view_attach/*', array('plugin'=>'Attach','controller' => 'Attachments', 'action' => 'viewAttach'));;
Router::connect('/deleteAtt/*', array('plugin'=>'Attach','controller' => 'Attachments', 'action' => 'attachDelete'));;