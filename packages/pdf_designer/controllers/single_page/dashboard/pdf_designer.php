<?php

/**
 * @project:   PDFDesignerObject (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Controller\SinglePage\Dashboard;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Page\Controller\DashboardPageController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Package\PdfDesigner\Src\PDFDesigner as PDFDesignerObject;

class PdfDesigner extends DashboardPageController
{
    public function importGoogleFont($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "font" => PDFDesignerObject::getInstance($templateId)->googleImportFont(
                    $this->post("fontName")
                )
            )
        ));
    }
    
    public function addFont($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "font" => PDFDesignerObject::getInstance($templateId)->addFont()
            )
        ));
    }

    public function removeFont($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "success" => PDFDesignerObject::getInstance($templateId)->removeFont(
                        $this->post("id")
                )
            )
        ));
    }

    public function getFonts($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "fonts" => PDFDesignerObject::getInstance($templateId)->getCustomFonts(false)
            )
        ));
    }

    public function getDocument($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "document" => PDFDesignerObject::getInstance($templateId)->getDocument()
            )
        ));
    }

    public function addBox($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "boxId" => PDFDesignerObject::getInstance($templateId)->addBox(
                        $this->post("x"), $this->post("y"), $this->post("width"), $this->post("height")
                )
            )
        ));
    }

    public function moveBox($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "success" => PDFDesignerObject::getInstance($templateId)->moveBox(
                        $this->post("boxId"), $this->post("x"), $this->post("y")
                )
            )
        ));
    }

    public function resizeBox($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "success" => PDFDesignerObject::getInstance($templateId)->resizeBox(
                        $this->post("boxId"), $this->post("width"), $this->post("height")
                )
            )
        ));
    }

    public function removeBox($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "success" => PDFDesignerObject::getInstance($templateId)->removeBox(
                        $this->post("boxId")
                )
            )
        ));
    }

    public function saveDocument($templateId)
    {
        return new JsonResponse(array(
            "response" => array(
                "document" => PDFDesignerObject::getInstance($templateId)->saveDocument(
                        $this->post()
                )
            )
        ));
    }

    public function preview($templateId)
    {
        PDFDesignerObject::getInstance($templateId)->outputPDF(
            json_decode(PDFDesignerObject::getInstance($templateId)->getTemplateEntity()->getSampleData(), true)
        );
    }

    public function duplicate($templateId)
    {
        PDFDesignerObject::getInstance($templateId)->duplicateTemplate();

        $this->redirect("/dashboard/pdf_designer");
    }

    public function export($templateId)
    {
        PDFDesignerObject::getInstance($templateId)->exportTemplate();
    }

    public function add()
    {
        $templateId = PDFDesignerObject::getInstance($templateId)->createEmptyTemplate();

        $this->redirect("/dashboard/pdf_designer/edit", $templateId);
    }

    public function remove($templateId)
    {
        PDFDesignerObject::getInstance($templateId)->removeTemplate();

        $this->redirect("/dashboard/pdf_designer");
    }

    public function edit($templateId)
    {
        $template = PDFDesignerObject::getInstance($templateId)->getTemplateEntity();

        $this->set("templateId", $templateId);
        $this->set("pdfDesigner", PDFDesignerObject::getInstance());
        $this->set("paperTypes", $template->getPaperTypesSimple());
        $this->set("boxTypes", PDFDesignerObject::getInstance()->getBoxEditors());

        $this->set('pageTitle', t("PDF Designer - Edit Template - %s", $template->getTemplateTitle()));

        $this->requireAsset("pdf_designer");
        $this->requireAsset("jsonmate");
        $this->requireAsset("javascript", "mustache");
        $this->requireAsset("javascript", "jquery-loading-overlay");
        $this->requireAsset('core/file-manager');

        $this->render("/dashboard/pdf_designer/edit");
    }

    public function view()
    {
        $this->set("templates", PDFDesignerObject::getInstance()->getTemplates());
        $this->render("/dashboard/pdf_designer/overview");
    }
}
