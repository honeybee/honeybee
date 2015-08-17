```php
        $user_art = $this->getServiceLocator()->getUserAggregateRootType();

        $fss = $this->getServiceLocator()->getFilesystemService();

        var_dump(
            "Schemes:",
            $fss->getSchemes(),

            "User temp storage prefix:",
            $fss->getTempPrefix($user_art),

            "Test for getTempScheme and getConnectorName",
            $this->getServiceLocator()->getConnectorService()->getConnector(
                $fss->getConnectorName('usertempfiles')
            )->getName() === $this->getServiceLocator()->getConnectorService()->getConnector(
                $fss->getConnectorName($fss->getTempScheme($user_art))
            )->getName(),

            "Test for Filesystem getting by prefix and scheme",
            $fss->getFilesystem('files') === $fss->getFilesystem($fss->getPrefix()),

            "Default main storage filesystem:",
            $fss->getFilesystem($fss->getPrefix()),

            "Files in temporary storage",
            $fss->listFiles($fss->getTempPrefix(), true),

            "Test for aggregate root type specific storage listing the same files:",
            $fss->listFiles('userfiles://', true) === $fss->listFiles($fss->getPrefix($user_art), true),

            "Paths of the main temporary filesystem storage:",
            $fss->listPaths($fss->getTempPrefix($user_art), true),

            "Putting foo.txt into the temporary user storage:",
            $fss->put(
                $fss->createTempUri('foo.txt', $user_art),
                'some content for the file'
            ),

            "Reading foo.txt content from temporary user storage:",
            $fss->read(
                $fss->createTempUri('foo.txt', $user_art)
            ),

            "List of all paths from temporary user storage:",
            $fss->listPaths($fss->getTempPrefix($user_art), true)
        );

        $foo = $fss->createTempUri('foo.txt', $user_art);
        $bar = $fss->createUri('bar.txt');
        if ($fss->has($foo) && !$fss->has($bar)) {
            var_dump(
                "Copying $foo to $bar",
                $fss->copy($foo, $bar)
            );
        }

        var_dump("Deleting $foo", $fss->delete($foo));
        var_dump("Deleting $bar", $fss->delete($bar));

        for ($i=0; $i<100;$i++) {
            $foo = $fss->createUri($fss->generatePath($user_art->getAttribute('images'), 'publisher'));
            $fss->put($foo, 'hello from honeybee');
        }

        $foo = $fss->createTempUri($fss->generatePath($user_art->getAttribute('images'), 'publisher'), $user_art);
        $fss->put($foo, 'hello from honeybee');
        var_dump($fss->read($foo));

        $stream = $fss->readStream(
            $fss->createUri($user->getImage()->getLocation(), $user->getType())
        );

        $foo = $fss->createUri('publisher');
        $fss->emptyDir($foo);
```
