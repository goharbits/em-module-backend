
   @foreach ($data['emails'] as $email)
       <p><b>Subject: </b>{{ $email['subject']  }}</p>
       <p><b>Body: </b>{!! $email['body'] !!}</p>
   @endforeach
