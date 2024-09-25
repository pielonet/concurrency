1. b and c : See https://www.php.net/manual/en/parallel.run.php
2. d : A task scheduled for execution in parallel can not receive a variable by reference.
3. c : As a parallel Runtime needs time to bootstrap the first "1" will be echoed in the main thread.
4. a and d : A task scheduled for execution in parallel can not receive an object as argument. As parallel runtime needs time to bootstrap, "Alice" will be echoed in the main thread before the runtime error occurs in the parallel thread.
5. c : This is the correct way of passing an object as argument to a task scheduled for execution in parallel : serializing.