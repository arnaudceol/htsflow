#This is probably not the best way to go about this, but it basically works.
#Keep in mind that PERL's "rand()" function isn't really random, but for this purpose it doesn't really matter.
#Suggested usage: samtools view -h <in.bam> | perl random_line_extraction.using_ratio.pl <ratio in decimal form> | samtools view -bS - > <out.bam>

$num=shift;
if($num<1){
	while($line=<STDIN>) {
    		chomp($line);
    		if($line =~ m/^@/) {
        		print "$line\n";
   		}		
    		else {
        		my $rnum = rand();
        		if($rnum<=$num) {
            			print "$line\n";
        		}
    		}	
	}
}
else{
	die "Error: ratio greater than one."
}		
