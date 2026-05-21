import unittest

from knowledge.ingest import chunk_text, validate_chunking
from knowledge.search import lexical_score


class IngestChunkingTest(unittest.TestCase):
    def test_chunk_text_uses_configured_size_and_overlap(self):
        text = 'A' * 250 + 'B' * 250 + 'C' * 250

        chunks = chunk_text(text, chunk_size=300, overlap=50)

        self.assertEqual(chunks, [
            'A' * 250 + 'B' * 50,
            'B' * 250 + 'C' * 50,
            'C' * 250,
        ])

    def test_validate_chunking_rejects_invalid_values(self):
        with self.assertRaisesRegex(ValueError, 'pelo menos 200'):
            validate_chunking(199, 0)

        with self.assertRaisesRegex(ValueError, 'não pode ser negativo'):
            validate_chunking(700, -1)

        with self.assertRaisesRegex(ValueError, 'menor que chunk-size'):
            validate_chunking(700, 700)

    def test_exact_document_title_match_ranks_above_content_match(self):
        titled_document = lexical_score(
            'cebas',
            'A Certificação das Entidades Beneficentes de Assistência Social é concedida às OSCs.',
            {'title': 'CEBAS', 'original_name': 'cebas.txt'},
        )
        content_only_document = lexical_score(
            'cebas',
            'CEBAS CEBAS CEBAS CEBAS CEBAS mudança de competência para a concessão do CEBAS.',
            {'title': 'Aspectos históricos do CEBAS no SUAS', 'original_name': 'aspectos-historicos-cebas.pdf'},
        )

        self.assertGreater(titled_document, content_only_document)


if __name__ == '__main__':
    unittest.main()
