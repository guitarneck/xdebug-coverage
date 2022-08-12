<?php

const COVERAGE_COVERALLS_SKIP_MAIN = true;

/*
   'service_event_type' => static::defaultEvent,
   'run_at' => $this->adapter->date('Y-m-d H:i:s O'),
   'source_files' => $this->makeSourceElement($coverage),
   'git' => $this->makeGitElement()

   if ($this->repositoryToken !== null) {
      $root['repo_token'] = $this->repositoryToken;
   }

   {
      "service_job_id": "1234567890",
      "service_name": "travis-ci",
      "source_files": [
         {
            "name": "example.rb",
            "source_digest": "asdfasdf1234asfasdf2345",
            "coverage": [null, 1, null]
         },
         {
            "name": "lib/two.rb",
            "source_digest": "asdf1234asdfsdfggsfgd9423",
            "coverage": [null, 1, 0, null]
         }
      ]
   }
*/

class CoverallsFormat extends AbstractFormat
{
   protected $options = array();

   function __construct(array $params = null)
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.coveralls';

      if (!isset($params['jobId'])) $params['jobId'] = null;
      if (!isset($params['name']))  $params['name'] = '';
      if (!isset($params['token'])) $params['token'] = '';

      $this->options['service_job_id'] = $params['jobId'];
      $this->options['service_name']   = $params['name']; // "travis-ci"
      $this->options['repo_token']     = $params['token'];
   }

   function render ( array $datas ): string
   {
      $sourceFiles = array();

      ksort($datas);
      foreach ( $datas as $fname => $file )
      {
         if ( empty( $file['functions'] ) ) continue;

         $last    = $this->mainLine($file['lines']);
         $lines   = array_fill(0,$last,null); // Do we need al the source lines ??? yes !
         $lines   = array_replace($lines,$file['lines']);
         if ( COVERAGE_COVERALLS_SKIP_MAIN ) array_pop($lines);

         $lines = array_map(function($v){
            switch ( $v )
            {
               case -1: return 0;
               case -2: return null;
            }
            return $v;
         },$lines);

         $sourceFiles[] = array(
            "name"            => $this->relativeToProject($fname),
            "source_digest"   => md5(file_get_contents($fname)),
            "coverage"        => $lines
         );
      }

      $coveralls = array(
         "service_job_id"     => $this->options["service_job_id"],
         "service_name"       => $this->options["service_name"],
         "service_event_type" => 'manual',
         "run_at"             => date('Y-m-d H:i:s O'),
         "git"                => $this->coverage_coveralls_gitinfo(),
         "repo_token"         => $this->options["repo_token"],
         "source_files"       => $sourceFiles
      );

      return json_encode($coveralls,JSON_UNESCAPED_SLASHES);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_PARMAMETER_HEAD,DataFormater::class2format(__CLASS__),"[?][jobId=][&][name=][&][token=]")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"jobId : (string) The service job id.")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"name  : (string) The service name.")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"token : (string) The repository token.")
           . XDBGCOV_FORMAT_PARMAMETER_FOOT;
   }

   protected
   function coverage_coveralls_gitinfo (): array
   {
      $nul = PHP_OS !== 'WINNT' ? '/dev/null' : 'nul';

      $git_last_commit = 'git log -1 --pretty=format:\'{"id":"%H","author_name":"%aN","author_email":"%ae","committer_name":"%cN","committer_email":"%ce","message":"%s"}\''." 2>{$nul}";
      $git_last_branch = 'git log -1 --decorate-refs="refs/heads/*" --format=%D'." 2>{$nul}";
      $gitinfo = array();
      $gitinfo["head"]     = json_decode(@exec($git_last_commit));
      $gitinfo["branch"]   = @exec($git_last_branch);

      return $gitinfo;
   }

   protected
   function relativeToProject ( string $path ): string
   {
      static $root = null;

      if ( $root === null )
      {
         $root = $path;
         do {
            $root = dirname($root);
         } while ( ! empty($root) && ! file_exists($root.DIRECTORY_SEPARATOR.'composer.json') );
      }

      return str_replace('\\', '/', substr($path,strlen($root) + 1));
   }
}