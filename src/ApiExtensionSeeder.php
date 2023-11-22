<?php namespace Visiosoft\ApiExtension;

use Anomaly\Streams\Platform\Assignment\Contract\AssignmentRepositoryInterface;
use Anomaly\Streams\Platform\Database\Seeder\Seeder;
use Anomaly\Streams\Platform\Field\Contract\FieldRepositoryInterface;
use Anomaly\Streams\Platform\Stream\Contract\StreamRepositoryInterface;

class ApiExtensionSeeder extends Seeder
{
    public function run(
        FieldRepositoryInterface      $fieldRepository,
        AssignmentRepositoryInterface $assignmentRepository,
        StreamRepositoryInterface     $streamRepository
    )
    {
        $stream = $streamRepository->findBySlugAndNamespace('users', 'users');

        $user_fields = [
            [
                'slug' => 'apikey',
                'namespace' => 'users',
                'type' => 'anomaly.field_type.text',
                'name' => 'apikey',
            ],
            [
                'slug' => 'jwt',
                'namespace' => 'users',
                'type' => 'anomaly.field_type.text',
                'name' => 'jwt',
            ],
        ];

        foreach ($user_fields as $user_field) {
            $field = $fieldRepository->findBySlugAndNamespace($user_field['slug'], $user_field['namespace']);

            if (!$field) {
                $data = [
                    'name' => $user_field['name'],
                    'namespace' => $user_field['namespace'],
                    'slug' => $user_field['slug'],
                    'type' => $user_field['type'],
                    'locked' => false
                ];

                $field = $fieldRepository->create($data);
            }

            if (empty($assignmentRepository->findByStreamAndField($stream, $field))) {
                $assignmentRepository->create([
                    'stream_id' => $stream->getId(),
                    'field_id' => $field->getId()
                ]);
            }
        }
    }
}
