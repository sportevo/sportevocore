<?
/*
  $Id: attributeManager.php,v 1.0 21/02/06 Sam West$

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Released under the GNU General Public License
  
  French translation for AJAX Attribute Manager V2.7.1
  Fichier traduit par Zardhoz le 04/03/08
  http://www.Francesco Rossi-fr.info/forum/
*/

//attributeManagerPrompts.inc.php

define('AM_AJAX_YES', 'Oui');
define('AM_AJAX_NO', 'Non');
define('AM_AJAX_UPDATE', 'Mettre � jour');
define('AM_AJAX_CANCEL', 'Annuler');
define('AM_AJAX_OK', 'OK');

define('AM_AJAX_SORT', 'Tri:');
define('AM_AJAX_TRACK_STOCK', 'Suivre le Stock?');
define('AM_AJAX_TRACK_STOCK_IMGALT', 'Suivre le Stock de cet attribut?');

define('AM_AJAX_ENTER_NEW_OPTION_NAME', 'Entrez le nom de la nouvelle option');
define('AM_AJAX_ENTER_NEW_OPTION_VALUE_NAME', 'Entrez le nom de la nouvelle valeur d\'option');
define('AM_AJAX_ENTER_NEW_OPTION_VALUE_NAME_TO_ADD_TO', 'Entrez le nom de la nouvelle valeur d\'option � ajouter � %s');

define('AM_AJAX_PROMPT_REMOVE_OPTION_AND_ALL_VALUES', 'Voulez vous vraiment supprimer l\'option %s ainsi que toutes les valeurs d\'options li�es � ce produit?');
define('AM_AJAX_PROMPT_REMOVE_OPTION', 'Voulez vous vraiment supprimer la valeur d\'option %s de ce produit?');
define('AM_AJAX_PROMPT_STOCK_COMBINATION', 'Voulez vous vraiment supprimer cette combinaison de stock de ce produit?');

define('AM_AJAX_PROMPT_LOAD_TEMPLATE', 'Voulez vous vraiment charger le Mod�le %s? <br>Ceci �crasera les options courantes de ce produit et ne peut �tre annul�.');
define('AM_AJAX_NEW_TEMPLATE_NAME_HEADER', 'Entrez le nom du nouveau Mod�le ou');
define('AM_AJAX_NEW_NAME', 'Nouveau Nom:');
define('AM_AJAX_CHOOSE_EXISTING_TEMPLATE_TO_OVERWRITE', '<br>choisissez un Mod�le existant � remplacer');
define('AM_AJAX_CHOOSE_EXISTING_TEMPLATE_TITLE', 'Mod�les existants:');
define('AM_AJAX_RENAME_TEMPLATE_ENTER_NEW_NAME', 'Entrez le nouveau nom du Mod�le %s');
define('AM_AJAX_PROMPT_DELETE_TEMPLATE', 'Voulez vous vraiment supprimer le Mod�le %s?<br>Ceci ne peut �tre annul�!');

//attributeManager.php

define('AM_AJAX_ADDS_ATTRIBUTE_TO_OPTION', 'Ajouter la valeur d\'option s�lectionn�e � l\'option %s');
define('AM_AJAX_ADDS_NEW_VALUE_TO_OPTION', 'Ajouter une nouvelle valeur d\'option � l\'option %s');
define('AM_AJAX_PRODUCT_REMOVES_OPTION_AND_ITS_VALUES', 'Supprimer l\'option %1$s et les %2$d valeurs d\'options li�es � ce produit');
define('AM_AJAX_CHANGES', 'Changements');
define('AM_AJAX_LOADS_SELECTED_TEMPLATE', 'Charger le Mod�le s�lectionn�');
define('AM_AJAX_SAVES_ATTRIBUTES_AS_A_NEW_TEMPLATE', 'Sauvegarder les attributs courants dans un nouveau Mod�le');
define('AM_AJAX_RENAMES_THE_SELECTED_TEMPLATE', 'Renommer le Mod�le s�lectionn�');
define('AM_AJAX_DELETES_THE_SELECTED_TEMPLATE', 'Supprimer le Mod�le s�lectionn�');
define('AM_AJAX_NAME', 'Nom');
define('AM_AJAX_ACTION', 'Action');
define('AM_AJAX_PRODUCT_REMOVES_VALUE_FROM_OPTION', 'Supprimer la valeur d\'option %1$s de l\'option %2$s de ce produit');
define('AM_AJAX_MOVES_VALUE_UP', 'Augmenter la valeur d\'option');
define('AM_AJAX_MOVES_VALUE_DOWN', 'Diminuer la valeur d\'option');
define('AM_AJAX_ADDS_NEW_OPTION', 'Ajouter une nouvelle option � la liste');
define('AM_AJAX_OPTION', 'Option:');
define('AM_AJAX_VALUE', 'Valeur:');
define('AM_AJAX_PREFIX', 'Pr�fixe:');
define('AM_AJAX_PRICE', 'Prix:');
define('AM_AJAX_SORT', 'Tri:');
define('AM_AJAX_ADDS_NEW_OPTION_VALUE', 'Ajouter une nouvelle valeur d\'option � la liste');
define('AM_AJAX_ADDS_ATTRIBUTE_TO_PRODUCT', 'Ajouter cet attribut au produit courant');
define('AM_AJAX_QUANTITY', 'Quantit�');
define('AM_AJAX_PRODUCT_REMOVE_ATTRIBUTE_COMBINATION_AND_STOCK', 'Supprimer cette combinaison d\'attribut et stock de ce produit');
define('AM_AJAX_UPDATE_OR_INSERT_ATTRIBUTE_COMBINATIONBY_QUANTITY', 'Mettre � jour ou ins�rer une combinaison d\'attribut avec une quantit� donn�e');

//attributeManager.class.php
define('AM_AJAX_TEMPLATES', '-- Liste Mod�les --');
?>