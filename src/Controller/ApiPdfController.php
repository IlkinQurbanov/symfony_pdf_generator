<?php


namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use OpenApi\Annotations as OA;

class ApiPdfController extends AbstractController
{
   /**
 * @Route("/api/generate-pdf", name="api_generate_pdf", methods={"POST"})
 * @OA\Post(
 *     path="/api/generate-pdf",
 *     summary="Generate PDF",
 *     description="Generates a PDF file based on the provided data.",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Pass data for PDF generation",
 *         @OA\JsonContent(
 *             type="object",
 *             required={"userName", "requestTitle", "requestDescription"},
 *             @OA\Property(property="userName", type="string", example="John Doe"),
 *             @OA\Property(property="requestTitle", type="string", example="Test Request"),
 *             @OA\Property(property="requestDescription", type="string", example="This is a sample description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="PDF generated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="PDF generated successfully"),
 *             @OA\Property(property="file_path", type="string", example="/public/pdfs/generated_pdf_123456789.pdf")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     )
 * )
 */

    public function generatePdf(Request $request): Response
    {
        // Получение данных из запроса
        $data = json_decode($request->getContent(), true);
        $userName = $data['userName'] ?? 'Anonymous';
        $requestTitle = $data['requestTitle'] ?? 'No Title';
        $requestDescription = $data['requestDescription'] ?? 'No Description';
        $pdfDate = (new \DateTime())->format('Y-m-d H:i:s');

        // Настройка Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Создание содержимого PDF
        $html = $this->renderView('pdf_template.html.twig', [
            'userName' => $userName,
            'requestTitle' => $requestTitle,
            'requestDescription' => $requestDescription,
            'pdfDate' => $pdfDate
        ]);

        // Генерация PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Сохранение PDF файла в папку
        $output = $dompdf->output();
        $pdfFilePath = $this->getParameter('kernel.project_dir') . '/public/pdfs/' . 'generated_pdf_' . time() . '.pdf';
        file_put_contents($pdfFilePath, $output);

        return new JsonResponse(['message' => 'PDF generated successfully', 'file_path' => $pdfFilePath], Response::HTTP_CREATED);
    }
}
